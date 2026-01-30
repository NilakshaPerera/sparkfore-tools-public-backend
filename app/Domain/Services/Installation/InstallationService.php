<?php

namespace App\Domain\Services\Installation;

use App\Domain\DataClasses\Installation\ChangeInstallationDiskSizeDto;
use App\Domain\DataClasses\Installation\DeleteInstallationDto;
use App\Domain\DataClasses\Installation\Installation;
use App\Domain\DataClasses\Installation\SetupFreeInstallationDto;
use App\Domain\DataClasses\Installation\SetupInstallationDto;
use App\Domain\Exception\SparkforeException;
use App\Domain\Models\CustomerProduct;
use App\Domain\Models\Hosting;
use App\Domain\Models\InstallationTargetType;
use App\Domain\Models\Product;
use App\Domain\Models\ProductAvailableCustomer;
use App\Domain\Models\PublicInstallation;
use App\Domain\Models\RemoteJob;
use App\Domain\Repositories\Installation\InstallationRepositoryInterface;
use App\Domain\Repositories\Remote\RemoteAdminRepository;
use App\Domain\Services\Remote\RemoteCallHandlerInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Domain\Services\ServiceApi\PrometheusApiServiceInterface;
use App\Domain\Repositories\Software\SoftwareRepositoryInterface;
use App\Domain\Services\ServiceApi\GiteaApiServiceInterface;
use App\Domain\Repositories\Plugin\PluginRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Log;

class InstallationService implements InstallationServiceInterface
{

    public function __construct(
        protected InstallationRepositoryInterface $installationRepository,
        protected PrometheusApiServiceInterface $prometheusApiService,
        protected SoftwareRepositoryInterface $softwareRepository,
        private GiteaApiServiceInterface $giteaApiService,
        protected PluginRepositoryInterface $pluginRepository
    ) {}

    /**
     * @return array
     */
    public function getFormCreate($customerId = 0)
    {
        $hostingProviders = $this->installationRepository->getHostingProviders();
        $hostingPackages = $this->installationRepository->getHostingPackages($customerId);

        $tHostingProviders = !empty($hostingProviders->transform(function ($item) {
            return [
                'value' => $item['id'],
                'text' => $item['name'],
                'active' => $item['active']
            ];
        })->toArray()) ? $hostingProviders : [];
        $tHostingPackages = !empty($hostingPackages) ? $hostingPackages->transform(function ($item) {
            $data = [
                'value' => $item['id'],
                'text' => $item['name'],
                'hosting_provider_id' => $item['hosting_provider_id'],
                'disk_size' => $item['disk_size'],
                'description' => $item['description'],
            ];
            if (auth()->user()->role->name != 'admin') {
                $data['production_price'] = 0;
                $data['staging_price'] = 0;
            } else {
                $data['production_price'] = $item['production_price_month'];
                $data['staging_price'] = $item['staging_price_month'];
            }
            return $data;
        })->toArray() : [];

        $productPackages = Product::select('id', 'pipeline_name')
            ->with([
                'productPlugins' => function ($query) {
                    $query->select('product_id', 'plugin_id', 'selected_version');
                },
                'productPlugins.plugin' => function ($query) {
                    $query->select('id', 'name');
                },
                'productSoftwares' => function ($query) {
                    $query->select('product_id', 'software_id', 'supported_version');
                },
                'productSoftwares.software' => function ($query) {
                    $query->select('id', 'name');
                }
            ]);

        if (auth()->user()->role->name != 'admin') {
            $productPackages = $productPackages->whereHas('productCustomer', function ($query) {
                $query->where('customer_id', auth()->user()->customer_id);
            });
        }

        return [
            'hosting_packages' => $tHostingPackages,
            'hosting_providers' => $tHostingProviders,
            'product_packages' => $productPackages->get(),
        ];
    }

    /**
     * @param $params
     * @return array|LengthAwarePaginator
     */
    public function listInstallations($params, $ccustomerId = null)
    {
        $paginatedResult = $this->installationRepository->listInstallations($params, $ccustomerId);

        return ($paginatedResult instanceof LengthAwarePaginator) ? $paginatedResult->through(function ($installation) {

            return [
                'id' => $installation->id,
                'product_id' => $installation->product_id,
                'url' => $installation->url,
                'software_version' => $installation->software_version,
                'customer' => $installation->customer ?: '',
                'customer_id' => $installation->customer_id ?? '',
                'status' => ucfirst($installation->status),
                'state' => $installation->state,
                'status_code' => $installation->status_code,
                'available_disk_space'
                => $installation->available_disk_space == -1 ? "N/A" : $installation->available_disk_space . 'GB',
                'last_build' => $installation->last_build ?: 'N/A',
                'branch' => ucfirst($installation->target_type) ?: 'N/A',
                'hosting_type' => $installation->hosting_type,
                'hosting_provider' => $installation->hosting_provider,
                'hosting_name' => $installation->hosting_name ?? 'N/A',
                'pro_pipeline_name' => $installation->pro_pipeline_name ?? 'N/A'
            ];
        }) : [];
    }

    /**
     * @param $params
     * @return mixed
     */
    public function storeInstallation($params)
    {

        try {

            DB::beginTransaction();

            Log::info("Creating installation ", [$params['customer_id'], $params['product_id']]);

            $url = $this->generateUrl($params);
            $hostingType = $this->determineHostingType($params['hosting_provider']);
            $customerProduct = $this->getCustomerProduct($params);
            $this->validateHostingPackage($params['hosting_package']);

            $urls = $this->generateUrls($params, $url);

            $remoteCallHandler = app(RemoteCallHandlerInterface::class);

            foreach ($urls as $type => $details) {

                $installation = $this->createInstallation($params, $customerProduct, $hostingType, $type, $details);
                $installationId = $this->installationRepository->storeInstallation(array_filter($installation->toArray()));
                Log::info("Installation created for $type url $details[url] ", [$installationId]);
                $this->handleInstallationType($params, $installationId, $remoteCallHandler);
                
            }

            DB::commit();

            return \App\Domain\Models\Installation::where('url', $url)->first();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error creating installation ", [$e->getMessage(), $params]);
            Log::error($e);
            throw $e;
        }
    }

    private function generateUrl($params)
    {
        return $params['domain_type'] == DOMAIN_TYPE_STANDARD
            ? ($params['sub_domain'] . '.' . SPARKFORE_DOMAIN) : $params['domain'];
    }

    private function determineHostingType($hostingProvider)
    {
        return $hostingProvider == 3 ? 2 : 1;
    }

    private function getCustomerProduct($params)
    {

        $productAvailableCustomer = ProductAvailableCustomer::where("customer_id", $params['customer_id'])
            ->where("product_id", $params['product_id'])
            ->first();

        $customerProduct = $this->installationRepository->getCustomerProductId(
            $params['customer_id'],
            $params['product_id']
        );

        if (!isset($productAvailableCustomer) && !isset($customerProduct)) {

            if (auth()->check() && auth()->user()->role->name == 'admin') {
                if (!isset($productAvailableCustomer)) {
                    Log::info("Creating customer product for product: ", [$params['customer_id'], $params['product_id'], auth()->user()->id]);
                    ProductAvailableCustomer::create([
                        "customer_id" => $params['customer_id'],
                        "product_id" => $params["product_id"]
                    ]);
                }

                if (!isset($customerProduct)) {
                    Log::info("Creating customer product for customer: ", [$params['customer_id'], $params['product_id'], auth()->user()->id]);
                    $product = Product::find($params['product_id']);
                    return CustomerProduct::create([
                        "customer_id" => $params['customer_id'],
                        "product_id" => $params["product_id"],
                        'label' => $product->pipeline_name ?? 'N/A',
                        'base_price_increase_yearly' => 0,
                        'base_price_per_user_increase_yearly' => 0,
                        'include_maintenance' => 0
                    ]);
                }
            } else {
                Log::error("Selected product is not available for the customer", [$params['customer_id'], $params['product_id'], auth()->user()->id]);
                throw new SparkforeException("Selected product is not available for the customer");
            }
        }
        return $customerProduct;
    }


    private function validateHostingPackage($hostingPackageId)
    {
        $hosting = Hosting::find($hostingPackageId);
        if (!isset($hosting->basePackage)) {
            throw new SparkforeException("Base package not available for the selected hosting package");
        }

        if (!isset($hosting->basePackage->ansible_package_id)) {
            throw new SparkforeException("Ansible package not available for the selected hosting package");
        }

        return $hosting;
    }

    private function generateUrls($params, $url)
    {
        $urls = [
            "production" => [
                'url' => $url
            ],
        ];

        if ($params["include_staging"] === true) {
            $stgUrl = $params['domain_type'] == DOMAIN_TYPE_STANDARD
                ? ($params['sub_domain'] . '.staging.' . SPARKFORE_DOMAIN)
                : str_replace('.', '-', $params['domain']) . '.staging.' . SPARKFORE_DOMAIN;

            $urls["staging"] = [
                'url' => $stgUrl
            ];
        }

        return $urls;
    }

    private function createInstallation($params, $customerProduct, $hostingType, $type, $details)
    {
        $installationTargetTypeId = InstallationTargetType::where('key', $type)->first()->id;

        return (new Installation())
            ->setCustomerProductId($customerProduct->id)
            ->setDomainType($params['domain_type'])
            ->setUrl($details["url"])
            ->setBillingTermsAgreement(true)
            ->setGeneralTermsAgreement(true)
            ->setIncludeBackup($params['include_backup'])
            ->setIncludeStagingPackage($params['include_staging'])
            ->setHostingId($params['hosting_package'])
            ->setHostingTypeId($hostingType)
            ->setHostingProviderId($params['hosting_provider'])
            ->setInstallationTargetTypeId($installationTargetTypeId);
    }

    private function handleInstallationType($params, $installationId, $remoteCallHandler)
    {
        switch ($params["installation_type"]) {
            case "free":
                $jobId = app(RemoteAdminRepository::class)->createRemoteJob(
                    REMOTE_JOB_TYPE_PUBLIC_INSTALLATION,
                    auth()->check() ? auth()->user()->id : -1,
                    $installationId
                );

                $freeInstallationDto = new SetupFreeInstallationDto();
                $dbInstallation = \App\Domain\Models\Installation::find($installationId);
                $freeInstallationDto->setFromInstallation($dbInstallation);
                $freeInstallationDto->setJobId($jobId);
                PublicInstallation::find($params["public_installation"])->update(['remote_job_id' => $jobId]);
                $response = $remoteCallHandler->setupFreeInstallation($freeInstallationDto);
                RemoteJob::find($jobId)->update(['callback_status' => $response->object()->status]);
                Log::info("Free installation setup called", [$installationId, $response->object()->status]);

                break;
            case "standard":

                $jobId = app(RemoteAdminRepository::class)->createRemoteJob(
                    REMOTE_JOB_TYPE_STANDARD_INSTALLATION,
                    auth()->check() ? auth()->user()->id : -1,
                    $installationId
                );
                $dbInstallation = \App\Domain\Models\Installation::find($installationId);
                $setupInstallationDto = new SetupInstallationDto(
                    $dbInstallation
                );
                Log::info("Creating standard installation setup DTO", [$installationId]);
                $setupInstallationDto->setJobId($jobId);
                $response = $remoteCallHandler->setupInstallation($setupInstallationDto);
                RemoteJob::find($jobId)->update(['callback_status' => $response->object()->status]);
                Log::info("Standard installation setup called", [$installationId, $response->object()->status]);

                break;
            default:
                Log::error("Installation type not found", [$params["installation_type"]]);
                break;
        }
    }

    /**
     * @param $params
     * @return mixed
     */
    public function editInstallation($params)
    {
        $currentInstallation = \App\Domain\Models\Installation::find($params['id']);
        if ($currentInstallation->disk_size != $params['disk_size']) {
            if ($currentInstallation->disk_size > $params['disk_size']) {
                throw new SparkforeException("Disk size can only be increased");
            }

            Log::info("Changing disk size for installation {$params['id']}; from {$currentInstallation->disk_size} GB to {$params['disk_size']} GB");

            $changeInstallationDiskSizeDto = new ChangeInstallationDiskSizeDto(
                $currentInstallation,
                intval($params['disk_size'])
            );

            $jobId = app(RemoteAdminRepository::class)->createRemoteJob(
                REMOTE_JOB_TYPE_CHANGE_DISK_SIZE,
                auth()->check() ? auth()->user()->id : -1,
                $params['id']
            );
            $changeInstallationDiskSizeDto->setJobId($jobId);
            $remoteCallHandler = app(RemoteCallHandlerInterface::class);
            $remoteCallHandler->changeInstallationDiskSize($changeInstallationDiskSizeDto);

            $currentInstallation->disk_size = intval($params['disk_size']);
            $currentInstallation->save();
        }
    }

    public function getInstallation($id)
    {

        // Product
        $installation = $this->installationRepository->getInstallation($id);

        $installationData = [
            'id' => $installation->id,
            'customer_id' => $installation->customer_id,
            'domain_type' => $installation->domain_type,
            'domain' => $installation->domain_type == DOMAIN_TYPE_CUSTOM ? $installation->url : null,
            'sub_domain' => $installation->domain_type == DOMAIN_TYPE_STANDARD ?
                str_replace('.' . SPARKFORE_DOMAIN, "", $installation->url) : null,
            'billing_terms' => $installation->billing_terms_agreement,
            'general_terms' => $installation->general_terms_agreement,
            'include_backup' => $installation->include_backup,
            'include_staging' => $installation->include_staging_package,
            'hosting_package' => $installation->hosting_id,
            'hosting_provider' => $installation->hosting_provider_id,
            'product_id' => $installation->product_id,
        ];

        if ($installation->disk_size == 0 || $installation->disk_size == null) {
            $installationData["disk_size"] = $installation->hosting_disk_size;
        } else {
            $installationData["disk_size"] = $installation->disk_size;
        }
        return $installationData;
    }

    private function getProductForInstallation($item)
    {
        $product = null;
        if (!empty($item['customerProduct']['product'])) {
            $softwareId = null;
            $pluginList = [];

            foreach ($item['customerProduct']['product']['productPlugins'] as $pluginData) {
                $pluginList[] = $pluginData['plugin'];
            }

            foreach ($item['customerProduct']['product']['productSoftwares'] as $softwareData) {
                if ($softwareData['environment'] == 'production') {
                    $softwareId = $softwareData['software_id'];
                }
            }


            $product = [
                "id" => $item['customerProduct']['product']['id'] ?? '',
                "software_id" => $softwareId ?? '',
                "supported_version" => $item['customerProduct']['product']['supported_version'] ?? '',
                "supported_version_type" => $item['customerProduct']['product']['supported_version_type'] ?? '',
                "plugins" => $pluginList
            ];
        }

        return $product;
    }

    public function getInstallationForManage($params)
    {

        $params['with'] = [
            'customerProduct',
            'customerProduct.product',
            'customerProduct.product.productPlugins',
            'customerProduct.product.productSoftwares',
            'customerProduct.product.productPlugins.plugin'
        ];

        $result = $this->installationRepository->getInstallationForManage($params);

        if ($result) {
            return $result->transform(function ($item) {
                $product = $this->getProductForInstallation($item);

                $software = $this->softwareRepository->getSoftwareById($product['software_id']);

                $gitBranchData = $this->giteaApiService->versionsAvailable($software->git_url, GIT_VERSION_TYPE_BRANCH);
                $gitTagData = $this->giteaApiService->versionsAvailable($software->git_url, GIT_VERSION_TYPE_TAG);
                $software->branch_versions = Arr::map($gitBranchData, function ($value) {
                    return [
                        'name' => $value['name']
                    ];
                });
                $software->tag_versions = Arr::map($gitTagData, function ($value) {
                    return [
                        'name' => $value['name']
                    ];
                });

                return [
                    "id" => $item['id'],
                    "product" => $product,
                    "software" => $software,
                    'types' => $this->pluginRepository->getGitVersionTypes()
                ];
            })->all();
        }
        return [];
    }


    /**
     * @param $params
     * @return string
     */
    public function deleteInstallations($installationId): string
    {
        $installation = \App\Domain\Models\Installation::find($installationId);

        if (auth()->user()->role->name != 'admin' && $installation->customerProduct->customer_id != auth()->user()->customer_id) {
            Log::error("Selected installation is not available for the customer", [$installationId, auth()->user()->id]);
            throw new SparkforeException("Selected installation is not available for the customer");
        }

        $jobId = app(RemoteAdminRepository::class)->createRemoteJob(
            REMOTE_JOB_TYPE_DELETE_INSTALLATION,
            auth()->check() ? auth()->user()->id : -1,
            $installationId
        );


        $deleteInstallationDto = new DeleteInstallationDto();
        $deleteInstallationDto->setFromInstallation($installation);
        $deleteInstallationDto->setJobId($jobId);
        $remoteCallHandler = app(RemoteCallHandlerInterface::class);
        $remoteCallHandler->deleteInstallation($deleteInstallationDto);

        return "Installation delete request has been sent";
    }

    public function handleAnsibleCallback($remoteJob)
    {
        if (REMOTE_JOB_TYPE_DELETE_INSTALLATION == $remoteJob->remoteJobType->key && $remoteJob->callback_status == "ANSIBLE_COMPLETE") {
            $this->installationRepository->deleteInstallations($remoteJob->reference_id);
            Log::warning("Installation deleted", [$remoteJob->reference_id]);
        } else {
            Log::info("Installation delete related callback", [$remoteJob->reference_id]);
        }
    }

    public function listInstallationsByStatus($status, $customerId = null)
    {
        return $this->installationRepository->listInstallationsByStatus($status, $customerId);
    }
}
