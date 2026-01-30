<?php

namespace App\Domain\Services\Product;

use App\Domain\DataClasses\Product\Product;
use App\Domain\DataClasses\CustomerProduct\CustomerProduct;
use App\Domain\DataClasses\Product\GenerateProductChangeDTO;
use App\Domain\Exception\SparkforeException;
use App\Domain\Jobs\ProductSync;
use App\Domain\Models\Customer;
use App\Domain\Models\Plugin;
use App\Domain\Models\PluginType;
use App\Domain\Models\Product as ModelsProduct;
use App\Domain\Models\CustomerProduct as ModelsCustomerProduct;
use App\Domain\Models\ProductChangeHistory;
use App\Domain\Models\RemoteJob;
use App\Domain\Models\RemoteJobType;
use App\Domain\Models\Software;
use App\Domain\Models\SoftwareVersion;
use App\Domain\Models\ProductAvailableCustomer;
use App\Domain\Repositories\Product\ProductRepositoryInterface;
use App\Domain\Repositories\Plugin\PluginRepositoryInterface;
use App\Domain\Repositories\Software\SoftwareRepositoryInterface;
use App\Domain\Repositories\Customer\CustomerRepositoryInterface;
use App\Domain\Services\Remote\RemoteAdminServiceInterface;
use App\Domain\Services\ServiceApi\GiteaApiServiceInterface;
use App\Domain\Services\Remote\RemoteCallHandlerInterface;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Cron\CronExpression;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Domain\Repositories\Remote\RemoteAdminRepositoryInterface;
use App\Domain\Traits\GitProductPackage;
use App\Domain\Traits\PipelineTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;
use Illuminate\Support\Str;
use Log;
use Illuminate\Support\Arr;
class ProductService implements ProductServiceInterface
{
    use PipelineTrait, GitProductPackage;

    public function __construct(
        protected ProductRepositoryInterface $productRepository,
        protected PluginRepositoryInterface $pluginRepository,
        protected SoftwareRepositoryInterface $softwareRepository,
        protected GiteaApiServiceInterface $giteaApiService,
        protected RemoteAdminServiceInterface $remoteAdminService,
        private RemoteCallHandlerInterface $remoteCallHandler,
        private RemoteAdminRepositoryInterface $remoteAdminRepository,
        protected CustomerRepositoryInterface $customerRepository

    ) {
    }

    public function getFormCreate()
    {
        // SW
        $software = Software::all();
        foreach ($software as &$sw) {
            $branches = $this->softwareRepository->getSoftwareVersions($sw->id, GIT_VERSION_TYPE_ID_BRANCH);

            $tags = $this->softwareRepository->getSoftwareVersions($sw->id, GIT_VERSION_TYPE_ID_TAG);

            $sw->branch_versions = $branches;
            $sw->tag_versions = $tags;
        }

        if (getNonAdminCustomerId() == null) {
            // Customers
            $customers = $this->productRepository->getCustomers();
            // Maintainers
            $maintainers = $this->productRepository->getMaintainers();
        } else {
            $customers = [];
            $maintainers = [];

        }


        // Environments
        $environments = config('environments');

        return [
            'software' => $software,
            'customers' => $customers,
            'environments' => $environments,
            'maintainers' => $maintainers,
            'types' => $this->pluginRepository->getGitVersionTypes()
        ];
    }

    private function getUnselectedPlugins($id, $env, $softwareId, $versionType, $supportedVersion)
    {
        $params['software_id'] = $softwareId;
        $params['sort_by'] = 'name';
        $params['sort_desc'] = 'asc';
        $params['version_type'] = $versionType;
        $params['supported_version'] = $supportedVersion;

        $plugins = $this->pluginRepository->getSoftwarePlugins($params, getNonAdminCustomerId());

        $selectedPluginsInEnv = $this->productRepository->getProductPluginsByEnvironment($id, $env);
        $selectedPluginIds = $selectedPluginsInEnv->pluck('id')->toArray();

        $selectedPlugins = $plugins->whereIn('id', $selectedPluginIds)
            ->map(function ($plugin) use ($selectedPluginsInEnv, $params) {
                $plugin['included'] = true;
                $selectedPlugin = $selectedPluginsInEnv->first(function ($selectedPlugin) use ($plugin) {
                    return $selectedPlugin['id'] === $plugin['id'];
                });
                $plugin['selected_version'] = $selectedPlugin ? $selectedPlugin['selected_version'] : null;
                $plugin['selected_version_type'] = $selectedPlugin ? $selectedPlugin['selected_version_type'] : null;

                $params["softwareVersionType"] = $params['version_type'];
                $params["softwareId"] = $params['software_id'];
                $params["moodleVersion"] = $params['supported_version'];

                $gitBranchData = $this->pluginRepository
                    ->getPluginVersions($plugin['id'], GIT_VERSION_TYPE_ID_BRANCH, $params);

                $gitTagData = $this->pluginRepository
                    ->getPluginVersions($plugin['id'], GIT_VERSION_TYPE_ID_TAG, $params);

                $plugin->branch_versions = $gitBranchData;
                $plugin->tag_versions = $gitTagData;

                return $plugin;
            });

        $unselectedPlugins = $plugins->whereNotIn('id', $selectedPluginIds)->map(function ($plugin) {
            $plugin['included'] = false;
            return $plugin;
        });

        return $selectedPlugins->concat($unselectedPlugins);
    }

    public function getFormSettingsCreate($env, $id)
    {
        $this->checkCustomerAccessForProduct($id);

        $productSoftware = $this->productRepository->getSoftwareByProductAndEnvironment($id, $env);
        $sortedPlugins = collect();
        // Customers
        $customers = $this->productRepository->getCustomers();

        $maintainers = $this->productRepository->getMaintainers();

        if (isset($productSoftware[0])) {
            $softwareId = $productSoftware[0]->software_id;
            $sortedPlugins = $this->getUnselectedPlugins(
                $id,
                $env,
                $softwareId,
                $productSoftware[0]->supported_version_type,
                $productSoftware[0]->supported_version
            );
        }

        return [
            'customers' => $customers,
            'plugins' => $sortedPlugins->values(),
            'maintainers' => $maintainers,
            'types' => $this->pluginRepository->getGitVersionTypes(),
        ];
    }

    public function getFormSettingsPluginsData($id, $env, $versionType, $softwareId, $supportedVersion)
    {
        $this->checkCustomerAccessForProduct($id);

        $unselectedPlugins = $this->getUnselectedPlugins(
            $id,
            $env,
            $softwareId,
            $versionType,
            $supportedVersion
        );
        return [
            "selected_plugins" => $this->getProductPlugins($id, $env, $versionType, $softwareId, $supportedVersion),
            "plugins" => $unselectedPlugins->values()
        ];
    }

    private function checkCustomerAccessForProduct($proId)
    {
        $productCustomers = $this->productRepository->getProductCustomers($proId);

        if (
            auth()->user()->role->name != 'admin'
            && getNonAdminCustomerId() != collect($productCustomers->pluck('id')->toArray())->first()
            ) {
            throw new SparkforeException("Forbidden", 403);
        }
    }

    public function getFormSettingsData($env, $id)
    {

        $this->checkCustomerAccessForProduct($id);

        // // customers
        $productCustomers = $this->productRepository->getProductCustomers($id);

        // Product
        $product = $this->productRepository->getProduct($id);

        // product plugin versions
        $productPluginVersions = $this->productRepository->getProductPluginVersions($id);

        $productPlugins = $this->getProductPlugins($id, $env);

        $productSoftware = $this->productRepository->getSoftwareByProductAndEnvironment($id, $env);
        $software = collect();
        if (isset($productSoftware[0])) {
            $software = $productSoftware[0];

            $softwareId = $software->software_id;

            $currentVersionId = SoftwareVersion::where('version_type', $software->supported_version_type)
                ->where('version_name', $software->supported_version)
                ->first()->version_id;


            $currentVersionId = substr($currentVersionId ?? '0', 0, 10);

            // Restrict downgrading the moodle version in product edit
            // https://git.autotech.se/LMS-Customer/sparkfore/issues/390
            $branches = $this->softwareRepository->getLatestSoftwareVersions(
                $softwareId,
                GIT_VERSION_TYPE_ID_BRANCH,
                $currentVersionId
            );
            $tags = $this->softwareRepository->getLatestSoftwareVersions(
                $softwareId,
                GIT_VERSION_TYPE_ID_TAG,
                $currentVersionId
            );

            $software->branch_versions = $branches;
            $software->tag_versions = $tags;
        }

        $productSyncStatus = "completed";

        $jobId = "ProductSync_" . hash('sha256', $product->git_url);
        if (Cache::has($jobId) && in_array(Cache::get($jobId), ['started', 'queued'])) {
            $productSyncStatus = "in-progress";
        }


        return [
            'product_id' => $product->id,
            'product_name' => $product->pipeline_name,
            'git_url' => $product->git_url,
            'maintainer_id' => strval($product->pipeline_maintainer_id),
            'development_schedule_hour' => $product->development_schedule_hour,
            'development_schedule_day' => $product->development_schedule_day,
            'development_schedule_month' => $product->development_schedule_month,
            'staging_schedule_hour' => $product->staging_schedule_hour,
            'staging_schedule_day' => $product->staging_schedule_day,
            'staging_schedule_month' => $product->staging_schedule_month,
            'production_schedule_hour' => $product->production_schedule_hour,
            'production_schedule_day' => $product->production_schedule_day,
            'production_schedule_month' => $product->production_schedule_month,
            'availability' => $product->availability,
            'software' => $software,
            'customer' => collect($productCustomers->pluck('id')->toArray())->first(),
            'selected_plugins' => $productPlugins,
            'plugin_versions' => collect($productPluginVersions->toArray()),
            'productSyncStatus' => $productSyncStatus,
        ];
    }

    private function getProductPlugins($id, $env, $versionType = null, $softwareId = null, $supportedVersion = null)
    {
        $productPlugins = [];
        if ($versionType && $softwareId && $supportedVersion) {
            $requiredVersionId = $this->pluginRepository
                ->getRequiredVersionId($versionType, $softwareId, $supportedVersion);
            $productPlugins = $this->productRepository
                ->getProductPluginsByEnvironmentAndRequiredVersion($id, $env, $requiredVersionId);
        } else {

            // product plugins
            $productPlugins = $this->productRepository->getProductPluginsByEnvironment($id, $env);
        }

        $params = [
            "softwareVersionType" => $versionType,
            "softwareId" => $softwareId,
            "moodleVersion" => $supportedVersion
        ];

        foreach ($productPlugins as $plugin) {
            $gitBranchData = $this->pluginRepository->getPluginVersions(
                $plugin->id,
                GIT_VERSION_TYPE_ID_BRANCH,
                $params
            );
            $gitTagData = $this->pluginRepository->getPluginVersions($plugin->id, GIT_VERSION_TYPE_ID_TAG, $params);
            $plugin->branch_versions = $gitBranchData;
            $plugin->tag_versions = $gitTagData;
        }

        return $productPlugins;
    }

    private function generateCronExpression($month, $hour, $day)
    {
        $cronExpression = "0 $hour $day $month *";
        if (!CronExpression::isValidExpression($cronExpression)) {
            throw new SparkforeException("Invalid month/day/hour values for the schedule.");
        }

        return $cronExpression;
    }

    public function listProducts($params, $customerId = null)
    {
        $params['with'] = [
            'maintainer'
        ];

        $paginatedResult = $this->productRepository->listProducts($params, $customerId);

        return ($paginatedResult instanceof LengthAwarePaginator) ? $paginatedResult->through(function ($product) {
            return [
                'id' => $product->id,
                'pipeline_name' => $product->pipeline_name,
                'release_notes' => $product->release_notes ?? 'N/A',
                'last_build' => $product->last_build,
                'availability' => $product->availability ?? 'Private',
                'maintainer' => $product->maintainer->name ?? 'N/A',
                'pipeline_deleted_at' => $product->pipeline_deleted_at,
                'git_deleted_at' => $product->git_deleted_at
            ];
        }) : [];
    }

    public function editCustomerProduct($params)
    {
        $customerProduct = (new CustomerProduct())
            ->setId($params['customer_product_id'])
            ->setLabel($params['product_label'])
            ->setIncludeMaintenance($params['include_maintenance']);

        return $this->productRepository->updateCustomerProduct(
            array_filter($customerProduct->toArray(), function ($value) {
                return $value !== null || $value === false;
            })
        );
    }


    public function getCustomerProduct($productId, $customerId)
    {
        $params['with'] = [
            'product'
        ];

        // Product
        $customerProduct = $this->productRepository->getCustomerProduct($customerId, $productId, $params);

        return [
            'customer_product_id' => $customerProduct->id,
            'product_label' => $customerProduct->label,
            'pipeline_name' => $customerProduct->product->pipeline_name,
            'include_maintenance' => $customerProduct->include_maintenance
        ];
    }

    public function getChangeHistory($product, $env, $page, $perPage, $sortBy, $sortDesc)
    {
        $this->checkCustomerAccessForProduct($product);
        return $this->productRepository->getChangeHistory($product, $env, $page, $perPage, $sortBy, $sortDesc);
    }


    /**
     * @param $params
     * @return void
     */
    public function storeProductFromGit($params)
    {

        $product = (new Product())
            ->setPipelineName($params['product_name'] ?? '')
            ->setAvailability($params['availability'] ?? '')
            ->setDevelopmentScheduledBuild(config('sparkfore.package_build.development_cron'))
            ->setStagingScheduledBuild(config('sparkfore.package_build.staging_cron'))
            ->setProductionScheduledBuild(config('sparkfore.package_build.production_cron'))
            ->setPipelineMaintainerId(1)
            ->setGitUrl($params['repo_url'])
            ->setLegacy($params['legacy'])
            ->setLegacyProductName(isset($params['legacy_product_name']) ? $params['legacy_product_name'] : null)
            ->setCreatedAt(Carbon::now()->toDateTimeString());

        // Product
        $productId = $this->productRepository->storeProduct(array_filter($product->toArray()));

        $this->storeProductSoftwares($productId, $params);
        // removed plugins add, will add from pluginSync job
        $this->storeProductCustomer($productId, $params);

        return $productId;
    }


    private function getCronExpression($params, $type)
    {
        $defaultCron = "";
        if ($type == "dev") {
            $type = "development";
        }
        $defaultCron = config("sparkfore.package_build.{$type}_cron");

        $scheduleHourKey = $type . '_schedule_hour';
        $scheduleDayKey = $type . '_schedule_day';
        $scheduleMonthKey = $type . '_schedule_month';

        if (isset($params[$scheduleHourKey]) && isset($params[$scheduleDayKey]) && isset($params[$scheduleMonthKey])) {
            return $this->generateCronExpression(
                $params[$scheduleMonthKey],
                $params[$scheduleHourKey],
                $params[$scheduleDayKey]
            );
        }
        return $defaultCron;
    }

    private function storeProductCustomer($productId, $params)
    {
        if (isset($params["customer_id"])) {
            $productCustomer = [
                'product_id' => $productId,
                'customer_id' => $params["customer_id"],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];

            $this->productRepository->storeProductCustomers($productCustomer);
        }
    }



    private function storeProductSoftwares($productId, $params)
    {
        $softwares = [];
        foreach (config('environments') as $environment) {
            $softwares[] = [
                'product_id' => $productId,
                'software_id' => $params['software_id'],
                'supported_version' => $params['supported_version'],
                'supported_version_type' => $params['supported_version_type'],
                'environment' => $environment['value'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }
        $this->productRepository->storeProductSoftwares($softwares);
    }

    private function storeProductPlugins($productId, $params)
    {
        $plugins = [];
        foreach (config('environments') as $environment) {
            foreach ($params['plugins'] as $plugin) {
                $plugins[] = [
                    'product_id' => $productId,
                    'plugin_id' => $plugin["id"],
                    'selected_version' => $plugin["selected_version"],
                    'selected_version_type' => $plugin["selected_version_type"],
                    'environment' => $environment['value'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
        }

        if (!empty($plugins)) {
            $this->productRepository->storeProductPlugins($plugins);
        }
    }


    /**
     * @param $params
     * @return void
     */
    public function storeProduct($params)
    {
        $response = [];
        try {
            DB::beginTransaction();

            if (!$this->isProductNameAvailable($params['product_name'])) {
                throw new SparkforeException("Given product name already exists.", 422);
            }

            $baseProduct = $this->softwareRepository->getSoftware($params['software_id']);
            $customerNameSlug = "";
            $customerName = "";
            $gitProName = "";

            if ($params['availability'] == 'private') {
                if (isset($params['customer'])) {
                    $customer = Customer::find($params['customer']);
                    $customerNameSlug = $customer->slugified_name;
                    $customerName = $customer->name;
                    $gitProName = $this->getGitNameOfProduct(
                        $customer->slugified_name,
                        $baseProduct->name,
                        $params['product_name']
                    );
                } else {
                    throw new SparkforeException("Customer is not selected for private product", 422);
                }
            } else {
                $gitProName = $this->getGitNameOfProduct("shared", $baseProduct->name, $params['product_name']);
                $customerNameSlug = "shared";
                $customerName = "shared";
            }

            $params['git_url'] = "https://git.autotech.se/LMS-Customer/" . $gitProName;
            $params["customer_slug"] = $customerNameSlug;


            $developmentCronExpression = $this->getCronExpression($params, 'development');
            $stagingCronExpression = $this->getCronExpression($params, 'staging');
            $productionCronExpression = $this->getCronExpression($params, 'production');

            $product = (new Product())
                ->setPipelineMaintainerId($params['maintainer_id'])
                ->setPipelineName($params['product_name'] ?? '')
                ->setGitUrl($params['git_url'])
                ->setReleaseNotes($params['release_notes'] ?? '')
                ->setPipelineBuildStatus($params['pipeline_build_status'] ?? '')
                ->setLastBuild($params['last_build'] ?? Carbon::now()->toDateTimeString())
                ->setDevelopmentScheduledBuild($developmentCronExpression ?? null)
                ->setStagingScheduledBuild($stagingCronExpression ?? null)
                ->setProductionScheduledBuild($productionCronExpression ?? null)
                ->setAvailability($params['availability'])
                ->setLegacy("false")
                ->setCreatedAt(Carbon::now()->toDateTimeString());

            // Product
            $productId = $this->productRepository->storeProduct(array_filter($product->toArray()));

            if ($params['availability'] == 'private') {
                $productCustomers = [
                    "product_id" => $productId,
                    "customer_id" => $params['customer']
                ];
                $this->productRepository->storeProductCustomers(array_filter($productCustomers));

                Log::info("Creating customer product for customer: ", [$params['customer'], $productId, auth()->user()->id]);
                $product = ModelsProduct::find($productId);
                ModelsCustomerProduct::create([
                    "customer_id" => $params['customer'],
                    "product_id" => $productId,
                    'label' => $params['product_name'] ?? 'N/A',
                    'base_price_increase_yearly' => 0,
                    'base_price_per_user_increase_yearly' => 0,
                    'include_maintenance' => 0
                ]);
            }

            $this->storeProductSoftwares($productId, $params);
            $this->storeProductPlugins($productId, $params);

            $response['repor_to_Create'] = 'LMS-Customer -'. basename($params['git_url']);


            //Create git repo
            $repos = $this->giteaApiService->createRepo('LMS-Customer', basename($params['git_url']));

            $response['repo'] = $repos;

            if (!empty($repos)) {

                $remoteRepositoryUrl = config('constants.HTTPS') . config('sparkfore.git_auth_token')
                    . "@git.autotech.se/LMS-Customer/" . basename($params['git_url']) . ".git";

                $cloneDirectory = base_path('repos');

                if (!File::exists($cloneDirectory)) {
                    File::makeDirectory($cloneDirectory, 0755, true);
                }

                $params['software']['supported_version_type'] = $params['supported_version_type'];
                $params['software']['supported_version'] = $params['supported_version'];

                $softwareName = Software::select("name")->find($params['software_id'])->name;

                $productNew = ModelsProduct::with("productCustomer.customer")->find($productId);
                $changeDto = new GenerateProductChangeDTO(
                    environment: "production",
                    oldPlugins: [],
                    oldSoftware: [],
                    saveChangeInDb: true,
                );
                $changeDto->setNewDbProductObj($productNew);
                $changeDto->setNewPlugins(
                    $this->productRepository->getProductPluginsByEnvironment($productId, "production")->toArray()
                );
                $changeDto->setNewSoftware([
                    "supported_version" => $params['supported_version'],
                    "supported_version_type" => $params['supported_version_type'],
                    "name" => $softwareName,
                ]);
                $productChanges = $this->getProductChanges($changeDto);

                $this->createAndPushRepo($params, $cloneDirectory, $remoteRepositoryUrl);

                $response['paramas'] = $params;
                $response['clonedir'] = $cloneDirectory;
                $response['remoterepo'] = $remoteRepositoryUrl;

                $this->addPluginVersion($params['plugins'], $params, $cloneDirectory);
                $this->addMoodleYaml($params, $cloneDirectory);
                $this->addAppYaml($params, $cloneDirectory);
                $this->addCustomerYaml($params, $cloneDirectory);
                $this->addSettingsYaml($params, $cloneDirectory);

                $this->pushChanges($params, $cloneDirectory, $productChanges["commitMessage"]);
                $this->pushToDevelopStagingChanges(
                    $params,
                    $cloneDirectory,
                    changeHistory: [
                        "product_id" => $changeDto->getNewDbProductObj()->id,
                        "change" => $productChanges["commitMessage"],
                        "created_by" => auth()->user()->id
                    ]
                );

                return $response;

                if (File::exists($cloneDirectory . '/' . basename($params['git_url']))) {
                    File::deleteDirectory($cloneDirectory . '/' . basename($params['git_url']));
                }

                $user = Auth::user();
                $userId = $user->id;

                $this->remoteAdminService
                    ->createPipeline([
                        'user_id' => $userId,
                        'package_id' => $productId,
                        'legacy' => "false",
                        'customer' => $customerName,
                        'customer_slug' => $customerNameSlug,
                        'base_product' => $baseProduct->name,
                        'base_product_slug' => Str::slug($baseProduct->name),
                        'name' => $params['product_name'],
                        'name_slug' => Str::slug($params['product_name'])
                    ]);

                DB::commit();
                $this->giteaApiService->updateRepo(
                    $params['git_url'],
                    'develop'
                ); // setting the default branch to develop #442

                $productNew->plugin_changes = json_encode($productChanges);

                return $productId;
            } else {
                throw new SparkforeException("Product name already exists in GIT.", 422);
            }
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    public function createAndPushRepo($params, $cloneDirectory, $remoteRepositoryUrl)
    {
        $localRepository = $cloneDirectory . '/' . basename($params['git_url']);

        if (File::exists($localRepository)) {
            File::deleteDirectory($localRepository);
        }

        File::makeDirectory($localRepository);
        File::makeDirectory($localRepository . '/plugins');
        File::makeDirectory($localRepository . '/core');

        $this->runProcess(['git', 'config', '--global', '--add', 'safe.directory', $localRepository], base_path());
        $this->runProcess(['git', 'init'], $localRepository);
        $user = Auth::user();
        $this->runProcess(['git', 'config', 'user.email', $user->email], $localRepository);
        $this->runProcess(['git', 'config', 'user.name', $user->f_name], $localRepository);

        $readme = $this->giteaApiService->getContent(
            config('sparkfore.git_moodle_baseline'),
            'README.md'
        );
        $readmeContent = base64_decode($readme['content'] ?? '');

        File::put($localRepository . '/README.md', $readmeContent);
        $this->runProcess(['git', 'add', 'README.md'], $localRepository);
        $this->runProcess(['git', 'commit', '-m', 'Initial commit'], $localRepository);
        $this->runProcess(['git', 'remote', 'add', 'origin', $remoteRepositoryUrl], $localRepository);
        $this->runProcess(['git', 'push', '-u', 'origin', 'master'], $localRepository);
    }

    public function addMoodleYaml($params, $cloneDirectory)
    {
        $ymlFilePath = $cloneDirectory . '/' . basename($params['git_url']) . '/' . 'core' . '/' . 'moodle.yaml';

        $moodle = $this->giteaApiService->getContent(
            config('sparkfore.git_moodle_baseline'),
            'core/moodle.yaml'
        );
        $data = Yaml::parse(base64_decode($moodle['content'] ?? ''));

        $phpVersion = SoftwareVersion::where('software_id', $params['software_id'])
            ->where('version_name', $params['software']['supported_version'])
            ->where('version_type', $params['software']['supported_version_type'])
            ->first()
            ->php_version;
        $sectionIndex = null;

        $withoutQuotes = [];
        $withoutQuotes["'{phpVersion}'"] = $phpVersion;
        $withoutQuotes[config('constants.plugins.phpVersion')] = $phpVersion;

        if ($params['software']['supported_version_type'] == 1) {
            $sectionIndex = $params['software']['supported_version'];

            $newSection = [
                'moodle' => [
                    'version' => $params['software']['supported_version'],
                    'tag_prefix' => config('constants.plugins.branch'),
                    'php' => config('constants.plugins.phpVersion'),
                ],
            ];
        } else {
            preg_match(
                '/v(\d+)\.(\d+)\.(\d+)/',
                $params['software']['supported_version'],
                $matches
            );
            $majorVersion = $matches[1];
            $minorVersion = $matches[2];
            $patchVersion = $matches[3];

            $sectionIndex = $majorVersion . $minorVersion;

            $newSection = [
                'moodle' => [
                    'version' => [
                        'major' => (int) $majorVersion,
                        'minor' => (int) $minorVersion,
                        'patch' => (int) $patchVersion,
                    ],
                    'tag_prefix' => 'v',
                    'php' => config('constants.plugins.phpVersion'),
                ],
            ];
        }



        $data[$sectionIndex] = $newSection;
        $yamlContent = Yaml::dump($data, 4, 2);
        $yamlContent = $this->writeWithoutQuotes($withoutQuotes, $yamlContent);
        File::put($ymlFilePath, $yamlContent);
    }

    public function addAppYaml($params, $cloneDirectory)
    {
        // Target: {cloneDirectory}/{repo-name}/core/app.yaml
        $ymlFilePath = $cloneDirectory . '/' . basename($params['git_url']) . '/core/app.yaml';

        // Start from baseline if you have one, otherwise start empty
        // If you DON'T have a baseline, just keep $data = [];
        $data = [];
        try {
            $app = $this->giteaApiService->getContent(
                config('sparkfore.git_moodle_baseline'),
                'core/app.yaml'
            );
            $decoded = base64_decode($app['content'] ?? '');
            $parsed = $decoded ? Yaml::parse($decoded) : [];
            $data = is_array($parsed) ? $parsed : [];
        } catch (\Throwable $e) {
            $data = [];
        }

        // runtime_version (example wants 8.4). Prefer DB if you have it, otherwise fall back.
        $runtimeVersion = null;
        try {
            $runtimeVersion = SoftwareVersion::where('software_id', $params['software_id'])
                ->where('version_name', $params['software']['supported_version'])
                ->where('version_type', $params['software']['supported_version_type'])
                ->first()
                ?->php_version; // reusing your column; rename if you store runtime separately
        } catch (\Throwable $e) {
            // ignore
        }
        $runtimeVersion = $runtimeVersion ?: '8.4';

        // Build the app version structure to match your required output
        // Output example uses key "50" and patch "2+workplace"
        // We derive it from supported_version if possible; otherwise default to 5.0.
        $major = 5;
        $minor = 0;

        // Try to infer major/minor from something like "...500..." -> 5.0
        // (Keeps behavior reasonable if params change.)
        $supported = $params['software']['supported_version'] ?? '';
        if (preg_match('/(\d)(\d)(\d)/', $supported, $m)) {
            // e.g. "500" => 5,0,0
            $major = (int) $m[1];
            $minor = (int) $m[2];
        }

        $sectionIndex = (string) ($major . $minor); // e.g. "50"

        $newSection = [
            'app' => [
                'version' => [
                    'major' => $major,
                    'minor' => $minor,
                    'patch' => '2+workplace',
                ],
                'tag_prefix' => 'v',
                'runtime_version' => $runtimeVersion, // wanted: 8.4
            ],
        ];

        $data[$sectionIndex] = $newSection;

        // Dump YAML with depth=4, indent=2 (same style as your moodle.yaml output)
        $yamlContent = Yaml::dump($data, 4, 2);

        // Ensure directory exists
        File::ensureDirectoryExists(dirname($ymlFilePath));

        File::put($ymlFilePath, $yamlContent);
    }

    public function addCustomerYaml($params, $cloneDirectory)
    {
        $customer = $this->giteaApiService->getContent(
            config('sparkfore.git_moodle_baseline'),
            'core/customer.yml'
        );
        $parsed = Yaml::parse(base64_decode($customer['content'] ?? ''));
        $languages = $parsed['languages'];

        $ymlFilePath = $cloneDirectory . '/' . basename($params['git_url']) . '/' . 'core' . '/' . 'customer.yaml';

        $newSection = [
            'customer' => $params['customer_slug'],
            'image_name' => $params['product_name'],
            'languages' => $languages
        ];

        if ($params['availability'] == 'private') {
            $newSection['registry_project'] = $params['customer_slug'];
        } else {
            $newSection['registry_project'] = "shared";
        }

        $yamlContent = Yaml::dump($newSection, 4, 2);
        $yamlContent = str_replace("'$languages'", $languages, $yamlContent);

        File::put($ymlFilePath, $yamlContent);
    }

    public function addSettingsYaml($params, $cloneDirectory)
    {
        $ymlFilePath = $cloneDirectory . '/' . basename($params['git_url']) . '/' . 'core' . '/' . 'settings.yaml';

        $sectionIndex = null;
        $data = [];
        $newSection = [
            'settings' => [
                'name' => 'settings',
                'version' => 'master',
                'location' => 'LMS-Other/moodle-basic-settings',
                'tag_prefix' => config('constants.plugins.branch')
            ],
        ];

        if ($params['software']['supported_version_type'] == 1) {
            $sectionIndex = $params['software']['supported_version'];
        } else {
            preg_match('/v(\d+)\.(\d+)\.(\d+)/', $params['software']['supported_version'], $matches);
            $majorVersion = $matches[1];
            $minorVersion = $matches[2];

            $sectionIndex = $majorVersion . $minorVersion;
        }

        $data[$sectionIndex] = $newSection;
        $yamlContent = Yaml::dump($data, 4, 2);
        File::put($ymlFilePath, $yamlContent);
    }
    public function updateSettingsYaml($params, $cloneDirectory)
    {
        $ymlFilePath = $cloneDirectory . '/' . basename($params['git_url']) . '/' . 'core' . '/' . 'settings.yaml';

        $sectionIndex = null;
        $data = [];
        $newSection = [
            'settings' => [
                'name' => 'settings',
                'version' => 'master',
                'location' => 'LMS-Other/moodle-basic-settings',
                'tag_prefix' => config('constants.plugins.branch')
            ],
        ];

        if ($params['software']['supported_version_type'] == 1) {
            $sectionIndex = $params['software']['supported_version'];
            $newSection['settings']['tag_prefix'] = config('constants.plugins.branch');
        } else {
            preg_match('/v(\d+)\.(\d+)\.(\d+)/', $params['software']['supported_version'], $matches);
            $majorVersion = $matches[1];
            $minorVersion = $matches[2];

            $sectionIndex = $majorVersion . $minorVersion;
            $newSection['settings']['tag_prefix'] = config('constants.plugins.branch');
        }

        if (File::exists($ymlFilePath)) {
            $contents = file_get_contents($ymlFilePath);
            $data = Yaml::parse($contents);
        }

        if (isset($data[$sectionIndex])) {
            $data[$sectionIndex] = array_merge($data[$sectionIndex], $newSection);
        } else {
            $data[$sectionIndex] = $newSection;
        }

        $yamlContent = Yaml::dump($data, 4, 2);
        File::put($ymlFilePath, $yamlContent);
    }


    protected function runProcess(array $command, string $workingDirectory)
    {
        $process = new Process($command);
        $process->setWorkingDirectory($workingDirectory);
        $process->run();

        // Executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }


    /**
     * @param $params
     * @return void
     */
    public function updateProduct($id, $params)
    {
        $this->checkCustomerAccessForProduct($id);

        if (isset($params['is_save_only']) && $params['is_save_only'] === "build") {
            $product = ModelsProduct::find($id);
            $productChanges = json_decode($product->plugin_changes, true);
            $this->buildProductRequest($product, $params, $productChanges);
            return "Product build is in-progress.";
        }

        // save the maintainanceID
        if (isset($params['maintainerId']) && !empty($params['maintainerId'])) {
            
            $this->productRepository->updateProduct([
                'pipeline_maintainer_id' => $params['maintainerId'],
            ], $id);

        }

        // if the customer was set, try and save the addtional settings in the settings page
        if (isset($params['customer']) && !empty($params['customer'])) {

            $p = ModelsProduct::find($id);

            $mcp = ModelsCustomerProduct::where([
                'customer_id' => $params['customer'],
                'product_id' => $id
            ])->first();

            // link the product with customer product
            if($p->availability == 'private' && !$mcp) {
                ModelsCustomerProduct::create([
                    "customer_id" => $params['customer'],
                    "product_id" => $id,
                    'label' => $p->pipeline_name ?? 'N/A',
                    'base_price_increase_yearly' => 0,
                    'base_price_per_user_increase_yearly' => 0,
                    'include_maintenance' => 0
                ]);
            }

            $avc = ProductAvailableCustomer::where([
                'product_id' => $id,
                'customer_id' => $params['customer']
            ])->first();

            // link the product with product is available for customer
            if($p->availability == 'private' && !$avc){
                ProductAvailableCustomer::create([
                    'product_id' => $id,
                    'customer_id' => $params['customer']
                ]);
            }

        }

        if(!isset($params['environment']) || empty($params['environment'])) {
            return "No environment selected for the update.";
        }

        $oldPlugins = $this->productRepository->getProductPluginsByEnvironment($id, $params['environment'])->toArray();

        $productSoftware = ModelsProduct::find($id)->productSoftwares()
            ->where("environment", $params['environment'])
            ->first();

        $oldSoftware = [
            "supported_version" => $productSoftware->supported_version,
            "supported_version_type" => $productSoftware->supported_version_type,
            "name" => $productSoftware->software->name,
        ];

        if (isset($params['software'])) {
            $softwares = [
                'software_id' => $params['software']['software_id'],
                'supported_version' => $params['software']['supported_version'],
                'supported_version_type' => $params['software']['supported_version_type'],
                'updated_at' => Carbon::now(),
            ];
            $this->productRepository->updateProductSoftware($params['software']['id'], $softwares);
        }


        if (isset($params['plugins']) && isset($params['environment'])) {

            $this->productRepository->deleteProductPluginsByEnvironment($id, $params['environment']);

            $plugins = [];
            foreach ($params['plugins'] as $plugin) {
                $plugins[] = [
                    'product_id' => $id,
                    'plugin_id' => $plugin["id"],
                    'selected_version' => $plugin["selected_version"],
                    'selected_version_type' => $plugin["selected_version_type"],
                    'environment' => $params['environment'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
            $this->productRepository->storeProductPlugins($plugins);
        }

        $product = new Product();
        $cronExpression = $this->getCronExpression($params, $params['environment']);
        if (isset($params['environment']) && $params['environment'] == 'dev') {
            $product->setDevelopmentScheduledBuild($cronExpression);
        } elseif (isset($params['environment']) && $params['environment'] == 'staging') {
            $product->setStagingScheduledBuild($cronExpression);
        } elseif (isset($params['environment']) && $params['environment'] == 'production') {
            $product->setProductionScheduledBuild($cronExpression);
        }

        if (isset($params['maintainerId'])) {
            $product->setPipelineMaintainerId($params['maintainerId']);
        }

        $this->productRepository->updateProduct(
            array_filter($product->toArray(), function ($value) {
                return $value !== null || $value === false;
            }),
            $id
        );

        $product = ModelsProduct::with("productCustomer.customer")->find($id);
        $changeDto = new GenerateProductChangeDTO(
            environment: $params['environment'],
            oldPlugins: $oldPlugins,
            oldSoftware: $oldSoftware,
            saveChangeInDb: true,
        );
        $changeDto->setNewDbProductObjects($product);
        $productChanges = $this->getProductChanges($changeDto);

        if ($productChanges["commitMessage"] !== "") {
            //Clone repository
            $repositoryUrl = str_replace(
                config('constants.HTTPS'),
                config('constants.HTTPS') . config('sparkfore.git_auth_token') . "@",
                $params['git_url']
            );
            $cloneDirectory = base_path('repos');

            if (!File::exists($cloneDirectory)) {
                File::makeDirectory($cloneDirectory, 0755, true);
            }

            $this->cloneRepo($params, $cloneDirectory, $repositoryUrl);

            // update yml file
            $isNewSoftwareVersion = $this->updateSoftwareVersion($params, $cloneDirectory);
            $this->updatePluginVersion($params, $cloneDirectory, $isNewSoftwareVersion);
            $this->deletePluginVersion($productChanges['removedPlugins'], $params, $cloneDirectory);
            $this->addPluginVersion($productChanges['addedPlugins'], $params, $cloneDirectory);
            $this->updateSettingsYaml($params, $cloneDirectory);

            // push yml file
            $this->pushChanges($params, $cloneDirectory, $productChanges["commitMessage"]);

            if (File::exists($cloneDirectory . '/' . basename($params['git_url']))) {
                File::deleteDirectory($cloneDirectory . '/' . basename($params['git_url']));
            }

        } else {
            Log::info("No changes found in product update for software and plugins.");
        }
        $productChanges = $this->mergeChanges(json_decode($product->plugin_changes ?: "", true), $productChanges);

        if (!$params['is_save_only']) {

            $this->buildProductRequest($product, $params, $productChanges);
        }
        $product->plugin_changes = [
            "softwareChanges" => $productChanges["softwareChanges"],
            "addedPlugins" => $productChanges["addedPlugins"],
            "removedPlugins" => $productChanges["removedPlugins"],
            "pluginChanges" => $productChanges["pluginChanges"],
        ];
        $product->save();


        return "Product updated successfully";
    }

    // TODO switch to this method and remove the old one after testing
    public function updateProduct_toswitch($id, array $params)
    {
        $this->checkCustomerAccessForProduct($id);

        // --- Normalize inputs ----------------------------------------------------
        $env        = Arr::get($params, 'environment');                 // dev|staging|production
        $mode       = Arr::get($params, 'is_save_only');                // bool|'build'|null
        $saveOnly   = is_bool($mode) ? $mode : in_array((string)$mode, ['1', 'true'], true);
        $gitUrl     = Arr::get($params, 'git_url');

        // Special "build now" branch (kept as-is)
        if ($mode == 'build') {
            $product         = ModelsProduct::findOrFail($id);
            $productChanges  = json_decode($product->plugin_changes ?? '[]', true) ?: [];
            $this->buildProductRequest($product, $params, $productChanges);
            return "Product build is in-progress.";
        }

        if (isset($params['maintainerId'])) {

            $this->productRepository->updateProduct([
                'pipeline_maintainer_id' => $params['maintainerId'],
            ], $id);

            // If maintainerId is the only change, return early.
            if (empty($env)
                && empty($params['software'])
                && empty($params['plugins'])
                && !isset($params['is_save_only']) // not trying to trigger a build/save mode
                && empty($gitUrl)) {
                return "Maintainer updated successfully";
            }
            // Else, continue to process env-specific changes if provided.
        }

        // We will only proceed with environment-coupled changes if env is set.
        if (empty($env)) {
            // If there's nothing to do without an environment, exit early.
            return "No environment specified. Nothing changed.";
        }

        // Fetch once and reuse
        $productModel = ModelsProduct::with(['productSoftwares.software', 'productCustomer.customer'])->findOrFail($id);

        // --- Snapshot "old" state only when needed -------------------------------
        $captureOldPlugins  = !empty($params['plugins']);
        $captureOldSoftware = !empty($params['software']);

        $oldPlugins = $captureOldPlugins
            ? $this->productRepository->getProductPluginsByEnvironment($id, $env)->toArray()
            : [];

        $productSoftwareRow = $productModel->productSoftwares()->where('environment', $env)->first();
        $oldSoftware = $captureOldSoftware && $productSoftwareRow
            ? [
                'supported_version'      => $productSoftwareRow->supported_version,
                'supported_version_type' => $productSoftwareRow->supported_version_type,
                'name'                   => optional($productSoftwareRow->software)->name,
            ]
            : [];

        // --- Apply incoming changes (software/plugins) ---------------------------
        if (!empty($params['software'])) {
            $sw = $params['software'];
            $this->productRepository->updateProductSoftware(
                $sw['id'],
                [
                    'software_id'             => $sw['software_id'],
                    'supported_version'       => $sw['supported_version'],
                    'supported_version_type'  => $sw['supported_version_type'],
                    'updated_at'              => Carbon::now(),
                ]
            );
        }

        if (!empty($params['plugins'])) {
            $this->productRepository->deleteProductPluginsByEnvironment($id, $env);

            $now     = Carbon::now();
            $plugins = [];
            foreach ($params['plugins'] as $plugin) {
                $plugins[] = [
                    'product_id'            => $id,
                    'plugin_id'             => $plugin['id'],
                    'selected_version'      => $plugin['selected_version'],
                    'selected_version_type' => $plugin['selected_version_type'],
                    'environment'           => $env,
                    'created_at'            => $now,
                    'updated_at'            => $now,
                ];
            }
            if ($plugins) {
                $this->productRepository->storeProductPlugins($plugins);
            }
        }

        // --- Product attributes (cron & maintainer) ------------------------------
        $productPatch = [];
        if (!empty($env)) {
            $cronExpression = $this->getCronExpression($params, $env);

            $p = new Product(); // DTO-ish container as in your original code
            if ($env == 'dev')        { $p->setDevelopmentScheduledBuild($cronExpression); }
            elseif ($env == 'staging'){ $p->setStagingScheduledBuild($cronExpression); }
            elseif ($env == 'production'){ $p->setProductionScheduledBuild($cronExpression); }

            if (isset($params['maintainerId'])) {
                $p->setPipelineMaintainerId($params['maintainerId']);
            }

            // Keep false values, drop nulls
            $productPatch = array_filter($p->toArray(), fn($v) => $v !== null || $v === false);
        }

        if (!empty($productPatch)) {
            $this->productRepository->updateProduct($productPatch, $id);
        }

        // --- Compute changes ------------------------------------------------------
        $productModel->loadMissing('productCustomer.customer'); // ensure relation
        $changeDto = new GenerateProductChangeDTO(
            environment: $env,
            oldPlugins:  $oldPlugins,
            oldSoftware: $oldSoftware,
            saveChangeInDb: true,
        );
        $changeDto->setNewDbProductObjects($productModel);
        $productChanges = $this->getProductChanges($changeDto);

        // --- Git/YAML updates only if there are changes --------------------------
        if (!empty($productChanges['commitMessage'])) {

            $repositoryUrl  = $gitUrl
                ? str_replace(config('constants.HTTPS'), config('constants.HTTPS') . config('sparkfore.git_auth_token') . "@", $gitUrl)
                : null;

            $cloneDirectory = base_path('repos');
            if (!File::exists($cloneDirectory)) {
                File::makeDirectory($cloneDirectory, 0755, true);
            }

            if ($repositoryUrl && $gitUrl) {
                $this->cloneRepo($params, $cloneDirectory, $repositoryUrl);

                $isNewSoftwareVersion = $this->updateSoftwareVersion($params, $cloneDirectory);
                $this->updatePluginVersion($params, $cloneDirectory, $isNewSoftwareVersion);
                $this->deletePluginVersion($productChanges['removedPlugins'] ?? [], $params, $cloneDirectory);
                $this->addPluginVersion($productChanges['addedPlugins'] ?? [], $params, $cloneDirectory);
                $this->updateSettingsYaml($params, $cloneDirectory);

                $this->pushChanges($params, $cloneDirectory, $productChanges['commitMessage']);

                $clonedPath = $cloneDirectory . '/' . basename($gitUrl);
                if (File::exists($clonedPath)) {
                    File::deleteDirectory($clonedPath);
                }
            }
        } else {
            Log::info("No changes found in product update for software and plugins.");
        }

        // --- Merge + persist plugin_changes --------------------------------------
        $existingChanges = json_decode($productModel->plugin_changes ?? '[]', true) ?: [];
        $merged          = $this->mergeChanges($existingChanges, $productChanges);

        // Trigger build unless "save only"
        if (!$saveOnly) {
            $this->buildProductRequest($productModel, $params, $merged);
        }

        $productModel->plugin_changes = [
            'softwareChanges' => $merged['softwareChanges'] ?? [],
            'addedPlugins'    => $merged['addedPlugins'] ?? [],
            'removedPlugins'  => $merged['removedPlugins'] ?? [],
            'pluginChanges'   => $merged['pluginChanges'] ?? [],
        ];
        $productModel->save();

        return "Product updated successfully";
    }

    private function buildProductRequest(ModelsProduct $product, $params, $productChanges = null)
    {
        $branch = $params['environment'];
        if ($params['environment'] == "dev") {
            $branch = "develop";
        }
        $this->triggerBuildPipeline($this->getBuildParams($product, $branch), $product->id, $productChanges);
    }

    private function setProductCustomerName(ModelsProduct $product, $params)
    {
        $customerName = "shared";
        $customerSlug = "shared";
        if ($product->availability == "private") {
            if ($product->productCustomer && $product->productCustomer->customer) {
                $customerName = $product->productCustomer->customer->name;
                $customerSlug = $product->productCustomer->customer->slugified_name;
            } else {
                throw new SparkforeException("Private product has no associated customer.", 422);
            }
        }
        $params['customer'] = $customerName;
        $params['customer_slug'] = $customerSlug;

        return $params;
    }


    public function cloneRepo($params, $cloneDirectory, $repositoryUrl)
    {

        if (File::exists($cloneDirectory . '/' . basename($params['git_url']))) {
            File::deleteDirectory($cloneDirectory . '/' . basename($params['git_url']));
        }
        $process = new Process(['git', 'clone', $repositoryUrl], $cloneDirectory);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // Set user email and name for the repository
        $user = Auth::user();

        $setEmailProcess = new Process(
            ['git', 'config', 'user.email', $user->email],
            $cloneDirectory . '/' . basename($params['git_url'])
        );
        $setEmailProcess->run();

        if (!$setEmailProcess->isSuccessful()) {
            throw new ProcessFailedException($setEmailProcess);
        }

        $setNameProcess = new Process(
            ['git', 'config', 'user.name', $user->f_name],
            $cloneDirectory . '/' . basename($params['git_url'])
        );
        $setNameProcess->run();

        if (!$setNameProcess->isSuccessful()) {
            throw new ProcessFailedException($setNameProcess);
        }

        // Checkout to the staging branch
        $branch = 'develop';

        if ($params['environment'] == 'staging') {
            $branch = 'staging';
        } elseif ($params['environment'] == 'production') {
            $branch = 'master';
        }

        $checkoutProcess = new Process(
            ['git', 'checkout', $branch],
            $cloneDirectory . '/' . basename($params['git_url'])
        );
        $checkoutProcess->run();

        if (!$checkoutProcess->isSuccessful()) {
            throw new ProcessFailedException($checkoutProcess);
        }
    }

    public function pushChanges($params, $cloneDirectory, $commitMessage)
    {
        // Check if there are any changes to the YAML file
        $diffProcess = new Process(
            ['git', 'status', '--porcelain'],
            $cloneDirectory . '/' . basename($params['git_url'])
        );
        $diffProcess->run();

        if (!$diffProcess->isSuccessful()) {
            throw new ProcessFailedException($diffProcess);
        }

        $output = $diffProcess->getOutput();
        if (empty($output)) {
            return "No changes to the YAML file. Nothing to commit.";
        }

        // Add the changes to Git
        $addProcess = new Process(['git', 'add', '.'], $cloneDirectory . '/' . basename($params['git_url']));
        $addProcess->run();

        if (!$addProcess->isSuccessful()) {
            throw new ProcessFailedException($addProcess);
        }


        $commitProcess = new Process(
            ['git', 'commit', '-m', $commitMessage],
            $cloneDirectory . '/' . basename($params['git_url'])
        );
        $commitProcess->run();

        if (!$commitProcess->isSuccessful()) {
            throw new ProcessFailedException($commitProcess);
        }

        // Push the changes
        $pushProcess = new Process(['git', 'push'], $cloneDirectory . '/' . basename($params['git_url']));
        $pushProcess->run();

        if (!$pushProcess->isSuccessful()) {
            throw new ProcessFailedException($pushProcess);
        }
    }

    public function pushToDevelopStagingChanges($params, $cloneDirectory, $changeHistory)
    {
        $branches = ['develop', 'staging'];

        foreach ($branches as $branch) {
            // Checkout the branch
            $checkoutProcess = new Process(
                ['git', 'checkout', '-b', $branch],
                $cloneDirectory . '/' . basename($params['git_url'])
            );
            $checkoutProcess->run();

            if (!$checkoutProcess->isSuccessful()) {
                throw new ProcessFailedException($checkoutProcess);
            }

            // Push the changes
            $pushProcess = new Process(
                ['git', 'push', 'origin', $branch],
                $cloneDirectory . '/' . basename($params['git_url'])
            );
            $pushProcess->run();

            if (!$pushProcess->isSuccessful()) {
                throw new ProcessFailedException($pushProcess);
            }

            if ($branch == "develop") {
                $changeHistory["branch"] = "dev";
            } else {
                $changeHistory["branch"] = $branch;
            }

            ProductChangeHistory::create($changeHistory);
        }
    }


    private function updateSoftwareVersion($params, $cloneDirectory)
    {
        $isNewSoftwareVersion = true;
        $ymlFilePath = $cloneDirectory . '/' . basename($params['git_url']) . '/' . 'core' . '/' . 'moodle.yaml';
        $contents = file_get_contents($ymlFilePath);
        $data = Yaml::parse($contents);

        $phpVersion = SoftwareVersion::where('software_id', $params['software']['software_id'])
            ->where('version_name', $params['software']['supported_version'])
            ->where('version_type', $params['software']['supported_version_type'])
            ->first()
            ->php_version;

        $withoutQuotes = [];
        $withoutQuotes["'{phpVersion}'"] = $phpVersion;
        $withoutQuotes[config('constants.plugins.phpVersion')] = $phpVersion;

        $sectionIndex = null;
        $newVersion = null;

        if ($params['software']['supported_version_type'] == 1) {

            $sectionIndex = $params['software']['supported_version'];
            $newVersion = $params['software']['supported_version'];

            $newSection = [
                'moodle' => [
                    'version' => $params['software']['supported_version'],
                    'tag_prefix' => config('constants.plugins.branch'),
                    'php' => config('constants.plugins.phpVersion'),
                ],
            ];
        } else {
            preg_match('/v(\d+)\.(\d+)\.(\d+)/', $params['software']['supported_version'], $matches);
            $majorVersion = $matches[1];
            $minorVersion = $matches[2];
            $patchVersion = $matches[3];
            $sectionIndex = $majorVersion . $minorVersion;
            $newVersion = $majorVersion . '.' . $minorVersion . '.' . $patchVersion;

            $newSection = [
                'moodle' => [
                    'version' => [
                        'major' => (int) $majorVersion,
                        'minor' => (int) $minorVersion,
                        'patch' => (int) $patchVersion,
                    ],
                    'tag_prefix' => 'v',
                    'php' => config('constants.plugins.phpVersion'),
                ],
            ];
        }

        // Unset the existing section if it exists
        if (isset($data[$sectionIndex])) {
            $oldSectionData = $data[$sectionIndex];
            if (in_array($oldSectionData['moodle']['tag_prefix'], ['v', ''])) {
                $previousVersion = $oldSectionData['moodle']['version']['major']
                    . '.' . $oldSectionData['moodle']['version']['minor']
                    . '.' . $oldSectionData['moodle']['version']['patch'];
            } else {
                $previousVersion = $oldSectionData['moodle']['version'];
            }

            if ($previousVersion == $newVersion) {
                // setting php version to the existing php version => https://git.autotech.se/LMS-Customer/sparkfore/issues/389
                $newSection['moodle']['php'] = $oldSectionData['moodle']['php'];
            }
            $isNewSoftwareVersion = false;
        }


        // Add the new section to the end of the array
        $data[$sectionIndex] = $newSection;
        $updatedYaml = Yaml::dump($data, PHP_INT_MAX, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
        $updatedYaml = $this->writeWithoutQuotes($withoutQuotes, $updatedYaml);
        file_put_contents($ymlFilePath, $updatedYaml);

        return $isNewSoftwareVersion;
    }

    private function updatePluginVersion($params, $cloneDirectory, $isNewSoftwareVersion)
    {
        $ymlFilePath = $cloneDirectory . '/' . basename($params['git_url']) . '/' . 'plugins';


        $pluginsList = $params['plugins'];

        if (!File::exists($ymlFilePath)) {
            File::makeDirectory($ymlFilePath, 0755, true);
        }

        // Get all YAML files in the directory
        $files = File::allFiles($ymlFilePath);

        foreach ($pluginsList as $plugin) {
            $pluginComponent = $this->productRepository->getProductPluginVersionsByVersion(
                $plugin['id'],
                $plugin['selected_version']
            );
            foreach ($files as $file) {

                // Check if the file has a .yml extension
                if ($file->getExtension() === 'yaml') {
                    $contents = File::get($file->getPathname());

                    // Parse the YAML content into an associative array
                    $data = Yaml::parse($contents);

                    foreach ($data as $section) {
                        if (is_array($section)) {
                            foreach ($section as $key => $value) {
                                if (
                                    is_array($value)
                                    && isset($value['location'])
                                    && $key == $pluginComponent["component"]
                                ) {
                                    $lastKey = array_key_last($data);
                                    $lastSectionAttributes = $data[$lastKey] ?? null;
                                    $sectionIndex = null;

                                    if ($params['software']['supported_version_type'] == 1) {
                                        $sectionIndex = $params['software']['supported_version'];
                                    } else {
                                        preg_match(
                                            '/v(\d+)\.(\d+)\.(\d+)/',
                                            $params['software']['supported_version'],
                                            $matches
                                        );
                                        $majorVersion = $matches[1];
                                        $minorVersion = $matches[2];
                                        $sectionIndex = $majorVersion . $minorVersion;
                                    }


                                    $withoutQuotes = [];

                                    $selectedVersion = $plugin['selected_version'];
                                    $withoutQuotes["'{version}'"] = $selectedVersion;
                                    $withoutQuotes[config('constants.plugins.version')] = $selectedVersion;

                                    $lastSectionAttributes[$pluginComponent["component"]]['version'] = config('constants.plugins.version');
                                    $lastSectionAttributes[$pluginComponent["component"]]['install_path'] =
                                        $this->getPluginInstallationPath(
                                            explode(
                                                '_',
                                                $pluginComponent['component']
                                            )[0]
                                        );

                                    $tagPrefix = '';
                                    // leaving tag_prefix empty for tags types https://git.autotech.se/LMS-Customer/sparkfore/issues/369
                                    if ($plugin['selected_version_type'] == 1) {
                                        $tagPrefix = config('constants.plugins.branch');
                                    }

                                    $lastSectionAttributes[$pluginComponent["component"]]['tag_prefix'] = $tagPrefix;


                                    $newSection = $lastSectionAttributes;
                                    $data[$sectionIndex] = $newSection;
                                    $updatedYaml = Yaml::dump(
                                        $data,
                                        PHP_INT_MAX,
                                        2,
                                        Yaml::DUMP_OBJECT_AS_MAP | Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK
                                    );
                                    $updatedYaml = $this->writeWithoutQuotes($withoutQuotes, $updatedYaml);
                                    file_put_contents($ymlFilePath . '/' . $file->getFilename(), $updatedYaml);

                                    break 2;
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    private function deletePluginVersion($removedPlugins, $params, $cloneDirectory)
    {
        $ymlFilePath = $cloneDirectory . '/' . basename($params['git_url']) . '/' . 'plugins';

        // Get all YAML files in the directory
        $files = File::allFiles($ymlFilePath);



        foreach ($removedPlugins as $plugin) {
            $component = Plugin::where("id", $plugin['id'])->first()->pluginVersions->first()->component;
            $pluginFile = $ymlFilePath . '/' . $component . '.yaml';
            $file = new \SplFileInfo($pluginFile);

            if (File::exists($pluginFile)) {
                $contents = File::get($file->getPathname());

                // Parse the YAML content into an associative array
                $data = Yaml::parse($contents);

                foreach ($data as $section) {
                    if (is_array($section)) {
                        foreach ($section as $key => $value) {

                            if (
                                is_array($value)
                                && isset($value['location'])
                                && explode("/", $value['location'])[1] === $plugin['name']
                            ) {
                                File::delete($file->getPathname());
                                break 2;
                            }
                        }
                    }
                }
            } else {
                Log::warning("Plugin file does not exist", [$pluginFile]);
            }
        }
    }


    private function addPluginVersion($addedPlugins, $params, $cloneDirectory)
    {
        foreach ($addedPlugins as $plugin) {

            $pluginComponent = $this->productRepository->getProductPluginVersionsByVersion(
                $plugin['id'],
                $plugin['selected_version']
            );

            $ymlFilePath = $cloneDirectory . '/'
                . basename($params['git_url'])
                . '/plugins/' . $pluginComponent['component']
                . '.yaml';

            $data = [];

            $sectionIndex = null;

            if ($params['software']['supported_version_type'] == 1) {
                $sectionIndex = $params['software']['supported_version'];
            } else {
                preg_match('/v(\d+)\.(\d+)\.(\d+)/', $params['software']['supported_version'], $matches);
                $majorVersion = $matches[1];
                $minorVersion = $matches[2];
                $sectionIndex = $majorVersion . $minorVersion;
            }
            $withoutQuotes = [];
            $selectedVersion = $plugin['selected_version'];
            $withoutQuotes["'{version}'"] = $selectedVersion;
            $withoutQuotes[config('constants.plugins.version')] = $selectedVersion;

            $tagPrefix = '';
            if ($plugin['selected_version_type'] == 1) {
                $tagPrefix = config('constants.plugins.branch');
            }  // leaving tag_prefix empty for tags types https://git.autotech.se/LMS-Customer/sparkfore/issues/369


            $data[$sectionIndex] = [
                $pluginComponent['component'] => [
                    'name' => str_contains($pluginComponent['component'], "_") ?
                        implode('_', array_slice(explode('_', $pluginComponent['component']), 1))
                        : $pluginComponent['component'],
                    'version' => config('constants.plugins.version'),
                    'install_path' => $this->getPluginInstallationPath(explode('_', $pluginComponent['component'])[0]),
                    'location' => ltrim(parse_url($plugin['git_url'], PHP_URL_PATH), '/'),
                    'tag_prefix' => $tagPrefix
                ],
            ];

            // Convert array to YAML
            $yamlContent = Yaml::dump($data, 4, 2);
            $yamlContent = $this->writeWithoutQuotes($withoutQuotes, $yamlContent);
            // Write YAML content to a new file
            File::put($ymlFilePath, $yamlContent);
        }
    }


    private function getPluginInstallationPath($component)
    {
        $instPath = $component;
        $pluginType = PluginType::where("component_name", $component)->first();
        if ($pluginType) {
            $dbPath = ltrim($pluginType->moodle_path, "/");
            Log::info("Selected component install path", [$component, $dbPath]);
            return $dbPath;
        }
        return $instPath;
    }


    /**
     * @param $params
     * @return void
     */
    public function updateProductEnvironmentPlugins($id, $params)
    {
        $this->checkCustomerAccessForProduct($id);

        $oldPlugins = $this->productRepository->getProductPluginsByEnvironment($id, $params['environment'])->toArray();
        $productSoftware = ModelsProduct::find($id)->productSoftwares()
            ->where("environment", $params['environment'])
            ->first();

        $oldSoftware = [
            "supported_version" => $productSoftware->supported_version,
            "supported_version_type" => $productSoftware->supported_version_type,
            "name" => $productSoftware->software->name,
        ];

        if (isset($params['software'])) {
            $softwares = [
                'software_id' => $params['software']['software_id'],
                'supported_version' => $params['software']['supported_version'],
                'supported_version_type' => $params['software']['supported_version_type'],
                'updated_at' => Carbon::now(),
            ];
            $this->productRepository->updateProductSoftwareByProductIdAndEnvironment(
                $id,
                $params['environment'],
                $softwares
            );
        }


        $product = ModelsProduct::find($id);
        $proSoftware = $product->productSoftwares()->where("environment", $params['environment'])->first();
        $changeDto = new GenerateProductChangeDTO(
            environment: $params['environment'],
            oldPlugins: $oldPlugins,
            oldSoftware: $oldSoftware,
            saveChangeInDb: true,
        );
        $changeDto->setNewDbProductObj($product);
        $changeDto->setNewPlugins($params['plugins']);
        $changeDto->setNewSoftware([
            "supported_version" => $proSoftware->supported_version,
            "supported_version_type" => $proSoftware->supported_version_type,
            "name" => $proSoftware->software->name,
        ]);

        $productChanges = $this->getProductChanges($changeDto);
        Log::info("Update product environment", [$productChanges]);

        if ($productChanges["commitMessage"] !== "") {
            //Clone repository
            $repositoryUrl = str_replace(
                config('constants.HTTPS'),
                config('constants.HTTPS') . config('sparkfore.git_auth_token') . "@",
                $params['git_url']
            );
            $cloneDirectory = base_path('repos');

            if (!File::exists($cloneDirectory)) {
                File::makeDirectory($cloneDirectory, 0755, true);
            }

            $this->cloneRepo($params, $cloneDirectory, $repositoryUrl);

            // update yml file
            $isNewSoftwareVersion = $this->updateSoftwareVersion($params, $cloneDirectory);
            $this->updatePluginVersion($params, $cloneDirectory, $isNewSoftwareVersion);
            $this->deletePluginVersion($productChanges['removedPlugins'], $params, $cloneDirectory);
            $this->addPluginVersion($productChanges['addedPlugins'], $params, $cloneDirectory);



            // push yml file
            $this->pushChanges($params, $cloneDirectory, $productChanges["commitMessage"]);

            if (isset($params['plugins'])) {
                $plugins = [];
                foreach ($params['plugins'] as $plugin) {
                    $plugins[] = [
                        'product_id' => $id,
                        'plugin_id' => $plugin["id"],
                        'selected_version' => $plugin["selected_version"],
                        'selected_version_type' => $plugin["selected_version_type"],
                        'environment' => $params['environment'],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }
                $this->productRepository->deleteProductPluginsByEnvironment($id, $params['environment']);

                $this->productRepository->storeProductPlugins($plugins);
            }
        } else {
            Log::info("No changes found in product update for software and plugins.", [$id, $params['environment']]);
            ;
        }

        return $id;
    }

    public function isProductNameAvailable($pipelineName)
    {
        $product = $this->productRepository->getProductByPipelineName($pipelineName);
        return $product ? false : true;
    }

    private function isCronDue($cronStr, $time)
    {
        if ($cronStr == null || !CronExpression::isValidExpression($cronStr)) {
            $cronStr = "0 0 * * *";
        }

        $cron = new CronExpression($cronStr);
        return $cron->isDue($time);
    }

    public function runScheduledBuilds()
    {
        $time = Carbon::now('UTC');

        $products = ModelsProduct::with('productSoftwares.software', 'productCustomer.customer')
            ->get();

        foreach ($products as $product) {
            if ($this->isCronDue($product->development_scheduled_build, $time)) {
                Log::info("Dev env build job product: " . $product->id);
                $this->triggerBuildPipeline($this->getBuildParams($product, "develop"), $product->id);
            }
            if ($this->isCronDue($product->staging_scheduled_build, $time)) {
                Log::info("Staging env build job product: " . $product->id);
                $this->triggerBuildPipeline($this->getBuildParams($product, "staging"), $product->id);
            }
            if ($this->isCronDue($product->production_scheduled_build, $time)) {
                Log::info("Product env build job product: " . $product->id);
                $this->triggerBuildPipeline($this->getBuildParams($product, "production"), $product->id);
            }
        }
    }


    public function triggerBuildPipelineRequest(ModelsProduct $prodProduct, $branch)
    {
        $this->checkCustomerAccessForProduct($prodProduct->id);
        $this->triggerBuildPipeline($this->getBuildParams($prodProduct, $branch), $prodProduct->id);
    }

    public function syncPluginsFromGit(ModelsProduct $prodProduct)
    {
        $this->checkCustomerAccessForProduct($prodProduct->id);
        $jobId = "ProductSync_" . hash('sha256', $prodProduct->git_url);
        if (Cache::has($jobId) && in_array(Cache::get($jobId), ['started', 'queued'])) {
            throw new SparkforeException("Product sync is already in progress.", 422);
        } else {
            dispatch(new ProductSync($prodProduct->git_url))->onQueue('pluginsSync');
            Cache::put($jobId, 'queued', 3600); // Cache for 1 hour
        }
    }


    /**
     * Writes the YAML content to a file without quotes.
     * fix for below issues
     * https://git.autotech.se/LMS-Customer/sparkfore/issues/388
     * https://git.autotech.se/LMS-Customer/sparkfore/issues/369
     *
     * @param array $replacements array holding key as the thing that need to replace and value will replace it
     * @param string $yamlContent The YAML content to be written.
     * @return string The YAMl content with replaced values.
     */
    private function writeWithoutQuotes($replacements, $yamlContent): string
    {
        foreach ($replacements as $key => $value) {
            $yamlContent = Str::replace($key, $value, $yamlContent);
        }

        return $yamlContent;
    }

    public function deleteProductModule(ModelsProduct $product, $module)
    {
        $this->checkCustomerAccessForProduct($product->id);
        if ($module == "git") {
            if (empty($product->git_deleted_at)) {
                $response = $this->giteaApiService->deleteRepo('LMS-Customer', basename($product->git_url));
            } else {
                throw new SparkforeException("Product Git package is already deleted.", 422);
            }

            if ($response === true) {
                $product->update([
                    "git_deleted_at" => Carbon::now()
                ]);
                Log::warning("Product Git package deleted ", [
                    $product->id,
                    $product->git_url,
                    $product->name,
                    Auth::user()->id
                ]);
                return "Product Git package deleted.";
            }
            throw new SparkforeException("Error deleteing Git repository,", 422);
        } elseif ($module == "pipeline") {
            return $this->deleteProductPipeline($product);
        } elseif ($module == "product") {
            if (empty($product->pipeline_deleted_at) || empty($product->git_deleted_at)) {
                throw new SparkforeException(
                    "Please delete pipeline and Git package before deleting the product.",
                    422
                );
            } else {
                $product->productPlugins()->delete();
                $product->productSoftwares()->delete();
                $product->productCustomer()->delete();
                $product->installations()->delete();
                $product->customerProducts()->delete();

                $remoteJobTypes = RemoteJobType::whereIn("key", [
                    REMOTE_JOB_TYPE_BUILD_PIPELINE,
                    REMOTE_JOB_TYPE_DELETE_PIPELINE
                ])
                    ->get()->pluck("id")->toArray();
                RemoteJob::whereIn("remote_job_type_id", $remoteJobTypes)->delete();
                $product->delete();
                Log::warning("Product deleted ", [$product->id, $product->git_url, $product->name, Auth::user()->id]);
                return "Product deleted sucessfully.";
            }
        }
    }

    private function deleteProductPipeline(ModelsProduct $product)
    {
        if (empty($product->pipeline_deleted_at)) {
            $params = $this->setProductCustomerName($product, []);
            Log::warning("Product pipeline delete request.", [
                $product->id,
                $product->git_url,
                $product->name,
                Auth::user()->id
            ]);
            return $this->remoteAdminService
                ->deletePipeline([
                    'user_id' => Auth::user()->id,
                    'package_id' => $product->id,
                    'customer' => $params["customer"],
                    'customer_slug' => $params["customer_slug"],
                    'base_product' => $product->productSoftwares->first()->software->name,
                    'base_product_slug' => Str::slug($product->productSoftwares->first()->software->name),
                    'name' => $product->pipeline_name,
                    'name_slug' => Str::slug($product->pipeline_name)
                ]);
        } else {
            throw new SparkforeException("Product pipeline is already deleted.", 422);
        }
    }
}
