<?php

namespace App\Domain\Repositories\Webhook;

use App\Domain\Models\Installation;
use App\Domain\Models\RemoteJob;
use App\Domain\Models\RemoteJobType;

class AlertProcessorRepository implements AlertProcessorRepositoryInterface
{
    public function __construct(
        private RemoteJob $remoteJob,
        private RemoteJobType $remoteJobType,
        private Installation $installation
        )
        {}

    /**
     * @param $referenceId
     * @param $jobType
     * @return mixed
     */
    public function getRemoteJob($referenceId, $jobType)
    {
        $jobTypeId = $this->remoteJobType::where('key', $jobType)->first()->id;
        return $this->remoteJob::where([
            'remote_job_type_id' => $jobTypeId,
            'id' => $referenceId
        ])->first();
    }

    /**
     * @param $installationId
     * @param $state
     * @param $status
     * @return mixed
     */
    public function updateInstallation($installationId, $state, $status)
    {
        return $this->installation->where('id', $installationId)->update([
            'state' => $state,
            'status' => $status
        ]);
    }
}
