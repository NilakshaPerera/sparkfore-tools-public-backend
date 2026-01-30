<?php

namespace App\Jobs\PluginSeeder;

use App\Domain\Services\ServiceApi\GiteaApiServiceInterface;
use DB;
use Exception;
use Illuminate\Support\Facades\Cache;
use Log;

class PluginTagsSeederJob extends PluginSeederJob
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
        Cache::lock($this->jobId . ':tag-lock')->forceRelease();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $lock = Cache::lock($this->jobId . ':tag-lock', 3600);

        if (!$lock->get()) {
            Log::warning("Plugin tag sync already running (lock not acquired)", [$this->repoURL]);
            return;
        }

        try {
            Cache::put($this->jobId, 'started', 3600);
            Cache::put($this->jobId . "-tag", 'started', 3600);

            Log::info("Starting tags seeding job for repoURL: {$this->repoURL}");
            try {
                $validTags = collect();
                $giteaService = app(GiteaApiServiceInterface::class);
                $tags = $giteaService->versionsAvailable($this->repoURL, GIT_VERSION_TYPE_TAG);
                foreach ($tags as $tag) {
                    $ref = $tag['name'] ?? null;
                    try {
                        $content = $giteaService->getContent($this->repoURL, 'version.php', $ref);
                        $decodedContent = base64_decode($content['content']);
                        $requiredMoodleVersionId = $this->extractSomething($decodedContent, '$plugin->requires');
                        if ($requiredMoodleVersionId == "") {
                            $requiredMoodleVersionId = $this->extractSomething($decodedContent, '$module->requires');
                        }
                        $requiredMoodleVersion = $this->findMoodleVersion($requiredMoodleVersionId);
                        $pluginComponent = $this->extractSomething($decodedContent, '$plugin->component');
                        $versionIdString = $this->extractSomething($decodedContent, '$plugin->version');
                        $versionId = preg_replace('/\D/', '', $versionIdString);

                        Log::info("Starting tags seeding job for repoURL:
                            {$this->repoURL}, {$ref}, {$requiredMoodleVersionId}");

                        DB::table('plugin_versions')->updateOrInsert(
                            [
                                'plugin_id' => $this->id,
                                'version_type' => 2,
                                'version_id' => $versionId,
                                'version_name' =>  $tag['name']
                            ],
                            [
                                'requires' => $requiredMoodleVersion,
                                'required_version_id' => $requiredMoodleVersionId,
                                'component' => $pluginComponent
                            ]
                        );


                        $latestTag = DB::table('plugin_versions')
                            ->where('plugin_id', $this->id)
                            ->where('version_type', 2)
                            ->where('version_id', $versionId)
                            ->where('version_name', $tag['name'])
                            ->latest()
                            ->first();

                        $validTags->push($latestTag->id);

                        Log::info("Completed seeding Tag: {$tag['name']}, Version: {$versionId} ");
                    } catch (\Throwable $e) {
                        Log::error("Error seeding Tag: {$tag['name']} | {$e->getMessage()}");
                        report($e);
                    }
                }
                $this->deleteExtraDBTags($validTags);
            } catch (\Throwable $th) {
                Log::error("Error seeding tags for repoURL: {$this->repoURL} | {$th->getMessage()}");
            }
            Cache::put($this->jobId . "-tag", 'completed', 3600);
            $this->setPluginSyncCompleteStatus($this->jobId);
        } finally {
            $lock->release();
        }
    }


    private function deleteExtraDBTags($validTags)
    {
        DB::table('plugin_versions')
            ->where('plugin_id', $this->id)
            ->where('version_type', 2)
            ->whereNotIn("id", $validTags)
            ->delete();

        Log::info("Deleted extra DB tags for repoURL: {$this->repoURL}");
    }
}
