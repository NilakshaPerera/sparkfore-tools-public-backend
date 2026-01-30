<?php

namespace App\Domain\Jobs;

use Artisan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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

class SyncSoftwaresJob implements ShouldQueue
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
        Artisan::call('db:seed', [
            '--class' => 'SoftwareTableSeeder',
            '--force' => true,
        ]);
    }
}
