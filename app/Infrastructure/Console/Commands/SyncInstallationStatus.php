<?php

namespace App\Infrastructure\Console\Commands;

use App\Domain\Jobs\ProcessSyncInstallationStatus;
use App\Domain\Repositories\Command\CommandRepositoryInterface;
use App\Domain\Services\ServiceApi\PrometheusApiServiceInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Log;

class SyncInstallationStatus extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-installation-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync statuses of all installation URL\'s';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('Installation Sync starting', [Cache::get("installationSync")]);
        if (Cache::has("installationSync") && Cache::get("installationSync") == "running") {
            Log::info("Installation sync job is running/completed");
            return;
        }
        Cache::put("installationSync", "running",  now()->addMinutes(10));
        $prometheusApiService = app(PrometheusApiServiceInterface::class);
        $commandRepository = app(CommandRepositoryInterface::class);
        $cleuraData = $prometheusApiService->getInstallations(
            'query=node_filesystem_avail_bytes{mountpoint="/",fstype!="rootfs"}', 'cleura'
        );
        $digitaloceanData = $prometheusApiService->getInstallations(
            'query=node_filesystem_avail_bytes{mountpoint="/",fstype!="rootfs"}', 'digitalocean'
        );
        $installations = $commandRepository->getInstallations();
        foreach ($installations as $installation) {
            
            // TODO: Remove this comments after testing
            $id = $installation->id;
            dispatch((new ProcessSyncInstallationStatus(
                $id, $cleuraData, $digitaloceanData, $commandRepository
            ))->onQueue("high")
            );

            Log::info('Installation Sync job send for installation id: ' . $id);

        }
    }
}
