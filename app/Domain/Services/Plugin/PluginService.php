<?php

namespace App\Domain\Services\Plugin;

use App\Domain\DataClasses\Plugin\Plugin;
use App\Domain\Exception\SparkforeException;
use App\Domain\Models\Plugin as ModelsPlugin;
use App\Domain\Repositories\Plugin\PluginRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use App\Domain\Services\ServiceApi\GiteaApiServiceInterface;
use App\Jobs\PluginSeeder\PluginBranchesSeederJob;
use App\Jobs\PluginSeeder\PluginTagsSeederJob;
use Illuminate\Support\Facades\Cache;
use Log;
use Illuminate\Support\Facades\DB;

class PluginService implements PluginServiceInterface
{
    public function __construct(
        private PluginRepositoryInterface $pluginRepository,
        protected GiteaApiServiceInterface $giteaApiService
    ) {
    }

    public function getSoftwarePlugins($params, $customerId = null)
    {
        $plugins = $this->pluginRepository->getSoftwarePlugins($params, $customerId);

        $plugins = $plugins->map(function ($plugin) {
            $plugin->name = Str::startsWith(strtolower($plugin->name), 'moodle-')
                ? Str::after(strtolower($plugin->name), 'moodle-') : $plugin->name;
            return $plugin;
        });

        return $plugins;
    }

    public function listPlugins($params, $customerId = null)
    {
        $params['with'] = ['softwares', 'customers'];
        $paginatedResult = $this->pluginRepository->listPlugins($params, $customerId);

        return ($paginatedResult instanceof LengthAwarePaginator)
            ? $paginatedResult->through(fn($plugin) => $this->transformPlugin($plugin))
            : [];
    }

    private function transformPlugin($plugin)
    {
        $syncStatus = $this->getSyncStatus($plugin->git_url);

        return [
            'id' => $plugin->id,
            'name' => $this->formatPluginName($plugin->name),
            'is_mirrored' => $plugin->is_mirrored ? 'Mirrored' : 'Pending',
            'git_url' => $plugin->git_url,
            'description' => $plugin->description,
            'connected_software' => $this->getConnectedSoftware($plugin),
            'availability' => $this->getAvailability($plugin),
            'supported_versions' => $plugin->version_supported,
            'price' => 'SEK ' . number_format($plugin->price, 2),
            'sync_status' => $syncStatus,
            "created_by" => $plugin->created_by,
        ];
    }

    private function getSyncStatus($gitUrl)
    {
        $jobId = "PluginSync_" . hash('sha256', $gitUrl);
        return (Cache::has($jobId) && in_array(Cache::get($jobId), ['started', 'queued']))
            ? "in-progress"
            : "completed";
    }

    private function formatPluginName($name)
    {
        return Str::startsWith(strtolower($name), 'moodle-')
            ? Str::after(strtolower($name), 'moodle-')
            : $name;
    }

    private function getConnectedSoftware($plugin)
    {
        return $plugin->softwares()->exists()
            ? implode(', ', $plugin->softwares->unique("id")->pluck('name')->all())
            : [];
    }

    private function getAvailability($plugin)
    {
        return ($plugin->availability == AVAILABILITY_PRIVATE && $plugin->customers()->exists())
            ? implode(', ', $plugin->customers->unique("id")->pluck('name')->all())
            : 'All';
    }

    /**
     * @return mixed
     */
    public function getFormCreate()
    {
        return [
            'types' => $this->pluginRepository->getGitVersionTypes(),
            'accessibility_types' => [
                ['text' => 'Public', 'value' => 'public'],
                ['text' => 'Private', 'value' => 'private']
            ],
            'availability' => [
                ['text' => 'All', 'value' => 'public'],
                ['text' => 'Selected', 'value' => 'private']
            ],
            'softwares' => $this->pluginRepository->getSoftwares(),
            'customers' => $this->pluginRepository->getCustomers(),
            'base_git_url' => config('sparkfore.github_url')
        ];
    }

    private function getReadmeContent($giteaRepositoryUrl)
    {
        $content['content'] = "";

        $targetName = 'readme';
        $repoContent = $this->giteaApiService->getContents($giteaRepositoryUrl);

        foreach ($repoContent as $object) {
            $nameWithoutExtension = Str::lower(pathinfo($object['name'], PATHINFO_FILENAME));
            if ($targetName === $nameWithoutExtension) {
                try {
                    $content = $this->giteaApiService->getContent($giteaRepositoryUrl, $object['name']);
                    break;
                } catch (\Throwable $e) {
                    Log::info("Error getting the content of git file", [$giteaRepositoryUrl, $object['name']]);
                }
                break;
            }
        }

        return $content;
    }
    /**
     * @param $params
     * @return void
     */
    public function storePlugin($params)
    {
        if(getNonAdminCustomerId()){
            $params['availability'] = "private";
            $params['accessibility_type'] = "private";
        }

        $giteaRepositoryOrgName = 'LMS-Mirror';
        $giteaRepositoryUrl = 'https://git.autotech.se/' . $giteaRepositoryOrgName . '/' . $params['name'];

        if ($params['accessibility_type'] == 'private') {
            $this->giteaApiService->migrateRepos(
                $params['git_url'],
                $params['name'],
                $giteaRepositoryOrgName,
                $params['access_token'],
                'x-oauth-basic'
            );
        } else {
            $this->giteaApiService->migrateRepos($params['git_url'], $params['name'], $giteaRepositoryOrgName);
        }

        // Update description using readme file
        $content = $this->getReadmeContent($giteaRepositoryUrl);



        // Multiple table inserts perform as transactions
        DB::transaction(function () use ($params, $giteaRepositoryUrl, $content) {
            $plugin = (new Plugin())
                ->setName($params['name'])
                ->setGitHubUrl($params['git_url'])
                ->setGitUrl($giteaRepositoryUrl)
                ->setAccessibilityType($params['accessibility_type'])
                ->setAccessToken($params['access_token'])
                ->setAvailability($params['availability'])
                ->setDescription(base64_decode($content['content'] ?? ''))
                ->setIsMirrored(true)
                ->setGitVersionTypeId($params['git_version_type_id'])
                ->setCreatedBy(auth()->user()->id);



            $pluginId = $this->pluginRepository->storePlugin(array_filter($plugin->toArray()));
            $dbPlugin = ModelsPlugin::find($pluginId);
            Log::info("Plugin created", [$dbPlugin->created_by]);
            $this->syncPlugin($dbPlugin);

            if (!empty($params['softwares'])) {
                foreach ($params['softwares'] as $software) {
                    $this->pluginRepository->storePluginSupportsSoftwares([
                        'plugin_id' => $pluginId,
                        'software_id' => $software
                    ]);
                }
            }

            if (!empty($params['customers'])) {
                foreach ($params['customers'] as $customer) {
                    $this->pluginRepository->storePluginAvailableCustomers([
                        'plugin_id' => $pluginId,
                        'customer_id' => $customer
                    ]);
                }
            }
        });
    }

    public function updatePlugin($params)
    {
        $this->checkCustomerAccessForPlugin(pluginId: $params['id']);
        // Multiple table inserts perform as transactions
        DB::transaction(function () use ($params) {

            $plugin = (new Plugin())
                ->setId($params['id'])
                ->setAvailability($params['availability'])
                ->setGitVersionTypeId($params['git_version_type_id']);

            $pluginId = $this->pluginRepository->updatePlugin(array_filter($plugin->toArray()));

            // purge
            $this->pluginRepository->purgePluginSupportsSoftwares($plugin->getId());
            foreach ($params['softwares'] as $software) {
                $this->pluginRepository->storePluginSupportsSoftwares([
                    'plugin_id' => $pluginId,
                    'software_id' => $software
                ]);
            }

            // purge
            $this->pluginRepository->purgePluginAvailableCustomers($plugin->getId());
            foreach ($params['customers'] as $customer) {
                $this->pluginRepository->storePluginAvailableCustomers([
                    'plugin_id' => $pluginId,
                    'customer_id' => $customer
                ]);
            }
        });

        $this->syncPlugin(ModelsPlugin::find($params['id']));
    }

    public function edit($id)
    {
        $plugin = $this->pluginRepository->edit($id);
        $this->checkCustomerAccessForPlugin(plugin: $plugin);

        $products = $plugin->productHasPlugins->map(function ($productHasPlugin) {
            if ($productHasPlugin->selected_version_type == 1) {
                $pluginVersion = "Branch: " . $productHasPlugin->selected_version;
            } else {
                $pluginVersion = "Tag: " . $productHasPlugin->selected_version;
            }
            return [
                "product_name" => $productHasPlugin->product->pipeline_name,
                "plugin_version" => $pluginVersion
            ];
        });

        return [
            'id' => $plugin->id,
            'name' => $plugin->name,
            'type' => $plugin->type,
            'git_url' => $plugin->git_url,
            'version_supported' => $plugin->version_supported,
            'git_version_type_id' => $plugin->git_version_type_id,
            'availability' => $plugin->availability,
            'softwares' => $plugin->softwares()->exists() ? $plugin->softwares->pluck('id')->unique()->all() : [],
            'customers' => $plugin->customers()->exists() ? $plugin->customers->pluck('id')->all() : [],
            'products' => $products,
            'price' => $plugin->price
        ];
    }

    public function getGitPluginName($url, $token)
    {
        try {

            $plugin = $this->pluginRepository->getPluginsByUrl(config('sparkfore.github_url') . $url);

            if (!$plugin->isEmpty()) {
                return [
                    'name' => null,
                    'message' => 'Pluging already mirrored'
                ];
            }

            if ($token) {
                $githubResponse = Http::withHeaders([
                    "Accept" => 'application/json',
                    "Authorization" => "Bearer " . $token
                ])->get(config('sparkfore.github_api_url') . $url);
            } else {
                $githubResponse = Http::withHeaders([
                    "Accept" => 'application/json',
                ])->get(config('sparkfore.github_api_url') . $url);
            }

            $githubRepoData = $githubResponse->json();

            return [
                'name' => $githubRepoData['name'],
                'message' => null
            ];
        } catch (\Exception $e) {
            return [
                'name' => null,
                'message' => 'Failed! Invalid repository'
            ];
        }
    }

    public function getPluginVersions($id, $params)
    {
        $branches = $this->pluginRepository->getPluginVersions($id, GIT_VERSION_TYPE_ID_BRANCH, $params);
        $tags = $this->pluginRepository->getPluginVersions($id, GIT_VERSION_TYPE_ID_TAG, $params);

        return [
            'id' => $id,
            'branches' => $branches,
            'tags' => $tags
        ];
    }

    public function syncPlugin(ModelsPlugin $plugin)
    {
        $this->checkCustomerAccessForPlugin(plugin: $plugin);
        $jobId = "PluginSync_" . hash('sha256', $plugin->git_url);

        if (Cache::has($jobId) && in_array(Cache::get($jobId), ['started', 'queued'])) {
            return "Plugin sync is already in progress";
        } else {
            PluginTagsSeederJob::dispatch($plugin->git_url, $plugin->id, $jobId)->onQueue('pluginsSync');
            Cache::put($jobId . "-tag", 'queued', 3600);
            Log::info("Plugin tags sync job dispatched for repo: {$plugin->git_url}");

            PluginBranchesSeederJob::dispatch($plugin->git_url, $plugin->id, $jobId)->onQueue('pluginsSync');
            Cache::put($jobId . "-branch", 'queued', 3600);
            Log::info("Plugin branches sync job dispatched for repo: {$plugin->git_url}");

            Cache::put($jobId, 'queued', 3600); // Cache for 1 hour
            return "Plugin sync job dispatched";
        }
    }

    public function getPluginByURL($gitUrl)
    {
        return $this->pluginRepository->getPluginByURL($gitUrl);
    }

    private function checkCustomerAccessForPlugin($pluginId=null, ModelsPlugin $plugin = null)
    {

        if (($plugin && $plugin->created_by !== auth()->user()->id) ||
            ($pluginId && !ModelsPlugin::where('id', $pluginId)
                ->where('created_by', auth()->user()->id)
                ->exists())
        ) {
            throw new SparkforeException("Forbidden", 403);
        }
    }
}
