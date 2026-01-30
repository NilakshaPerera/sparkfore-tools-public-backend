<?php

namespace App\Infrastructure\Console\Commands;

use App\Domain\Jobs\ProductSync;
use App\Domain\Services\ServiceApi\GiteaApiServiceInterface;
use App\Domain\Traits\GitProductPackage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Log;

class SyncProductPackages extends Command
{
    use GitProductPackage;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-product-packages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync product packages from the Git to database';

    /**
     * Execute the console command.
     */

    public function __construct(
        protected GiteaApiServiceInterface $giteaApiService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        Log::info("Starting run product packages from Git: command");
        $this->getGitProducts();
        Log::info("Completed run product packages from Git: command");
    }

    private function getGitProducts()
    {
        $page = 1;
        $repos = $this->giteaApiService->reposAvailable("LMS-Customer", $page);
        while (!empty($repos)) {
            foreach ($repos as $repo) {
                try {
                    $this->checkForCustomerYml($repo["html_url"], $this->giteaApiService);
                } catch (\Throwable $e) {
                    Log::error(
                        "Error checking Git repo whether product: "
                        . $e->getMessage() . " trace: " . $e->getTraceAsString()
                    );
                    continue;
                }

                $jobId = "ProductSync_" . hash('sha256', $repo["html_url"]);
                if (Cache::has($jobId) && in_array(Cache::get($jobId), ['started', 'queued'])) {
                    Log::info("Product sync is already in progress", [$repo["html_url"]]);
                } else {
                    dispatch(new ProductSync($repo["html_url"], repoObj: $repo))->onQueue('pluginsSync');
                    Cache::put($jobId, 'queued', 3600); // Cache for 1 hour
                    Log::info("Product sync job send to queue", [$repo["html_url"]]);
                }
            }
            $page += 1;
            $repos = $this->giteaApiService->reposAvailable("LMS-Customer", $page);
        }
    }
}
