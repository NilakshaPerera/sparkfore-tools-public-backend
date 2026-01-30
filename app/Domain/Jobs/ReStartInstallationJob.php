<?php

namespace App\Domain\Jobs;

use App\Domain\Services\Remote\RemoteAdminService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReStartInstallationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $instanceId;
    protected $branch;
    protected $version;
    /**
     * Create a new job instance.
     */
    public function __construct($instanceId, $branch, $version)
    {
        $this->instanceId = $instanceId;
        $this->branch = $branch;
        $this->version = $version;
    }

    public $tries = 2;

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        app(RemoteAdminService::class)->restartServer([
            'installation_id' => $this->instanceId,
            'environment' => $this->branch,
            'user_id' => 1,
            'version' => $this->version
        ]);
    }
}
