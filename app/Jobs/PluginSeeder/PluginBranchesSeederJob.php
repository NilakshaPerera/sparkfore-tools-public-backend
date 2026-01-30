<?php

namespace App\Jobs\PluginSeeder;

use App\Domain\Services\ServiceApi\GiteaApiServiceInterface;
use DB;
use Exception;
use Illuminate\Support\Facades\Cache;
use Log;

class PluginBranchesSeederJob extends PluginSeederJob
{
    protected  $repoURL;
    protected $id;
    protected $jobId;


    /**
     * Create a new job instance.
     */
    public function __construct($repoURL, $id, $jobId)
    {
        $this->repoURL = $repoURL;
        $this->id = $id;
        $this->jobId = $jobId;
    }

    public function failed(Exception $exception)
    {
        Cache::put($this->jobId, 'completed', 3600);
        $this->setPluginSyncCompleteStatus($this->jobId);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Cache::put($this->jobId, 'started', 3600);
        Cache::put($this->jobId . "-branch", 'started', 3600);

        Log::info("Starting branches seeding job for repoURL: {$this->repoURL}");
        try {
            $validBranches = collect();
            $giteaService = app(GiteaApiServiceInterface::class);
            $branches = $giteaService->versionsAvailable($this->repoURL, GIT_VERSION_TYPE_BRANCH);
            foreach ($branches as $branch) {
                Log::info("Seeding Branch: {$branch['name']}");
                $ref = $branch['name'] ?? null;
                try {
                    $content = $giteaService->getContent($this->repoURL, 'version.php', $ref);
                    $decodedContent = base64_decode($content['content']);
                    $requiredMoodleVersionId = $this->extractSomething($decodedContent, '$plugin->requires');
                    if ($requiredMoodleVersionId == "") {
                        $requiredMoodleVersionId = $this->extractSomething($decodedContent, '$module->requires');
                    }
                    $requiredMoodleVersion = $this->findMoodleVersion($requiredMoodleVersionId);
                    $versionIdString = $this->extractSomething($decodedContent, '$plugin->version');
                    $pluginComponent = $this->extractSomething($decodedContent, '$plugin->component');
                    $versionId = preg_replace('/\D/', '', $versionIdString);

                    DB::table('plugin_versions')->updateOrInsert(
                        [
                            'plugin_id' => $this->id,
                            'version_type' => 1,
                            'version_id' => $versionId,
                            'version_name' =>  $branch['name']
                        ],
                        [

                            'requires' => $requiredMoodleVersion,
                            'required_version_id' => $requiredMoodleVersionId,
                            'component' => $pluginComponent
                        ]
                    );

                    $latestBranch = DB::table('plugin_versions')
                        ->where('plugin_id', $this->id)
                        ->where('version_type', 1)
                        ->where('version_id', $versionId)
                        ->where('version_name', $branch['name'])
                        ->latest()
                        ->first();

                    $validBranches->add($latestBranch->id);

                    Log::info("Completed seeding Branch: {$branch['name']}, Version: {$versionId} ");
                } catch (\Throwable $e) {
                    Log::error("Error seeding branch: {$branch['name']} | {$e->getMessage()}");
                    report($e);
                }
            }

            $this->deleteExtraDbBranches($validBranches);
        } catch (\Throwable $th) {
            Log::error("Error seeding branches for repoURL: {$this->repoURL} | {$th->getMessage()}");
        }

        Cache::put($this->jobId . "-branch", 'completed', 3600);
        $this->setPluginSyncCompleteStatus($this->jobId);
    }


    private function deleteExtraDbBranches($validBranches)
    {

        DB::table('plugin_versions')
            ->where('plugin_id', $this->id)
            ->where('version_type', 1)
            ->whereNotIn("id", $validBranches)
            ->delete();

        Log::info("Deleted extra DB branches for repoURL: {$this->repoURL}");
    }
}
