<?php

namespace App\Domain\Jobs;

use App\Domain\Events\ProductBuildLogEvent;
use App\Domain\Models\ProductBuild;
use App\Domain\Models\RemoteJob;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

/**
 * Class SetProductBuildStatusJob
 *
 * This job is responsible for updating the status of product builds that have been in the 'RECEIVED' or 'PROCESSING' 
 * stages for more than one hour to 'TIMED-OUT'.
 *
 * @package App\Domain\Jobs
 *
 * @implements ShouldQueue
 */

class SetProductBuildStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * Create a new job instance.
     */
    public function __construct()
    {

    }

    public $tries = 2;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $stages = config("sparkfore.aapi_stages");
        $oneHourAgo = Carbon::now()->subHour();
        
        foreach ($stages as $stage) {
            $productBuilds = ProductBuild::whereIn($stage, ['RECEIVED', 'PROCESSING'])
            ->where('updated_at', '<', $oneHourAgo)
            ->get();

            foreach ($productBuilds as $productBuild) {
                $jobId = $productBuild->remoteJob->id;
                $productBuild->update([
                    $stage => 'TIMED-OUT'
                ]); 
                Log::info("ProductBuild status updated to TIMED-OUT for remote job id: $jobId");
                ProductBuildLogEvent::dispatch(RemoteJob::find($jobId));
            }
        }
        Log::info("SetProductBuildStatusJob completed");

        
    }
}
