<?php

namespace Database\Seeders;

use App\Domain\DataClasses\Plugin\PluginDescriptionDto;
use App\Domain\Services\OpenAI\SparkforeOpenAI;
use App\Domain\Services\ServiceApi\GiteaApiServiceInterface;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Jobs\PluginSeeder\PluginTagsSeederJob;
use App\Jobs\PluginSeeder\PluginBranchesSeederJob;
use Log;
use App\Domain\Models\Plugin;
use Illuminate\Support\Facades\Cache;

class PluginTableSeeder extends Seeder
{
    private $giteaService;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            Artisan::call('cache:clear');
            $output = Artisan::output();
            // Clearing cache to fix permission issue happen when cache files are overlapping
            Log::info('Cache cleared: ', [$output]);

            echo "Seeding plugins...\n";

            $contexts = [
                'LMS-Plugin',
                'LMS-Mirror-Manual',
                'LMS-Mirror',
                'LMS-Other'
            ];
            $validRepoUrls = collect();

            $this->giteaService = app(GiteaApiServiceInterface::class);

            foreach ($contexts as $ctx) {
                echo "Processing context: $ctx\n";

                $allRepos = [];
                $pageNumber = 1;

                //Get all repos
                do {
                    $repos = $this->giteaService->reposAvailable($ctx, $pageNumber);
                    echo "Processing page: $pageNumber\n";
                    echo "Number of repos: " . count($repos) . "\n";
                    if (!empty($repos)) {
                        $allRepos = array_merge($allRepos, $repos);
                        $pageNumber++;
                    }

                } while (!empty($repos));

                foreach ($allRepos as $repo) {
                    $baseRef = $this->getValidReferenceForRepo($repo['html_url']);
                    echo "Processing repo: {$repo['html_url']}, baseRef: $baseRef\n";

                    try {
                        $this->giteaService->getContent($repo['html_url'], 'version.php', $baseRef);
                    } catch (\Throwable $e) {
                        Log::info("This is not a moodle plugin", [$repo['html_url'], $e->getMessage(), $e->getTraceAsString()]);
                        $this->deletePlugin($repo['html_url'], -1);
                        continue;
                    }

                    $this->deleteDuplicatePlugins($repo['html_url']);

                    echo "Update or create data for repo: {$repo['html_url']}\n";
                    // Inserting data into the 'plugins' table
                    $dbPlugin = Plugin::updateOrCreate(
                        ['git_url' => $repo['html_url']],
                        [
                            'name' => $repo['name'],
                            'github_url' => $this->getGithubRepo($repo),
                            'git_version_type_id' => 1, // Branch by default
                            'description' => "",
                            'price' => 0,
                            'type' => 'free',
                            'availability' => 'public',
                            'is_mirrored' => true
                        ]
                    );
                    $this->setPluginDescription($dbPlugin);
                    DB::table('plugin_supports_softwares')->insertGetId([
                        'plugin_id' => $dbPlugin->id,
                        'software_id' => 1
                    ]);

                    $validRepoUrls->add($repo['html_url']);

                    $jobId = "PluginSync_" . hash('sha256', $repo['html_url']);

                    if (Cache::has($jobId) && in_array(Cache::get($jobId), ['started', 'queued'])) {
                        Log::info("Plugin sync is already in progress" . $repo['html_url']);
                    } else {
                        PluginTagsSeederJob::dispatch($repo['html_url'], $dbPlugin->id, $jobId)->onQueue('pluginsSync');
                        Cache::put($jobId . "-tag", 'queued', 3600);
                        Log::info("Plugin tags seeder job dispatched for repo: {$repo['html_url']}");

                        PluginBranchesSeederJob::dispatch($repo['html_url'], $dbPlugin->id, $jobId)->onQueue('pluginsSync');
                        Cache::put($jobId . "-branch", 'queued', 3600);
                        Log::info("Plugin branches seeder job dispatched for repo: {$repo['html_url']}");

                        Cache::put($jobId, 'queued', 3600); // Cache for 1 hour
                    }


                }
            }

            $this->removeExtraDBPlugins($validRepoUrls);
        } catch (\Throwable $e) {
            Log::error("Error in seeding plugins: " . $e->getMessage() . " trace: " . $e->getTraceAsString());
        }

    }

    private function removeExtraDBPlugins($validRepoUrls)
    {
        Log::info("Searching for extra DB plugins to delete");
        $extraDBPlugins = DB::table('plugins')
        ->whereNotIn("git_url", $validRepoUrls->toArray())
        ->pluck('git_url')
        ->toArray();

        foreach ($extraDBPlugins as $pluginGitUrl) {
            $this->deletePlugin($pluginGitUrl, -1);
        }

    }

    private function deletePlugin($gitUrl, $excludeId)
    {
        Log::info("Deleting duplicate plugins for $gitUrl");
        $pluginsToDelete = DB::table('plugins')
            ->where("git_url", $gitUrl)
            ->where("id", "!=", $excludeId)
            ->pluck('id')->toArray();

        foreach ($pluginsToDelete as $plugin) {

            Log::info("Starting deleting duplicate tags from products for plugin  id: $plugin");
            DB::table('product_has_plugins')
                ->where('plugin_id', $plugin)
                ->delete();

            Log::info("Starting deleting duplicate tags for plugin id: $plugin");
            DB::table('plugin_versions')
                ->where('plugin_id', $plugin)
                ->delete();

            Log::info("Starting deleting duplicate plugin supports softwares for plugin id: $plugin");
            DB::table('plugin_supports_softwares')
                ->where('plugin_id', $plugin)
                ->where('software_id', 1)
                ->delete();


            DB::table('plugins')->where("id", $plugin)->delete();
            Log::info("Deleted duplicate plugin id: $plugin");

        }

    }

    private function deleteDuplicatePlugins($gitUrl)
    {
        Log::info("Deleting duplicate plugins for $gitUrl");
        $latestPlugin = Plugin::where("git_url", $gitUrl)->latest()->first();

        if ($latestPlugin) {
            Log::info("Latest DB plugin for $gitUrl is $latestPlugin->id");
            $this->deletePlugin($gitUrl, $latestPlugin->id);
        }


    }

    private function getValidReferenceForRepo($repoURL)
    {
        try {
            $branches = $this->giteaService->versionsAvailable($repoURL, GIT_VERSION_TYPE_BRANCH);

            if (count($branches) > 0) {
                return $branches[0]['name'];
            }
        } catch (\Throwable $e) {
            Log::info("Error getting ref branch for $repoURL", [$e->getMessage(), $e->getTraceAsString()]);
        }


        try {
            $tags = $this->giteaService->versionsAvailable($repoURL, GIT_VERSION_TYPE_TAG);
            if (count($tags) > 0) {
                return $tags[0]['name'];
            }
        } catch (\Throwable $e) {
            Log::info("Error getting ref tag for $repoURL", [$e->getMessage(), $e->getTraceAsString()]);
        }
    }

    private function getGithubRepo($repoData)
    {
        if (isset($repoData['original_url']) && strpos($repoData['original_url'], 'github.com') !== false) {
            return $repoData['original_url'];
        } else if (isset($repoData['clone_url']) && strpos($repoData['clone_url'], 'github.com') !== false) {
            return $repoData['clone_url'];
        }
    }

    private function setPluginDescription($dbPlugin)
    {
        if ($dbPlugin->description == "" && $dbPlugin->github_url != "") {
            $pluginDescriptionDto = new PluginDescriptionDto($dbPlugin->id, $dbPlugin->github_url);
            $dbPlugin->description = (new SparkforeOpenAI())->getPluginDescription($pluginDescriptionDto);
            $dbPlugin->save();
        }

    }
}
