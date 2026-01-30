<?php

namespace App\Domain\Traits;

use App\Domain\Models\RemoteJob;

trait InstallationTrait
{

    /**
     * Retrieve the installation status for a given installation ID and remote job types.
     *
     * @param int $installationId The ID of the installation.
     * @param array $remoteJobTypes An array of remote job type IDs to filter by.
     * @return string|null The callback status of the latest RemoteJob matching the criteria, or null if none found.
     */
    private function getInstallationStatus(int $installationId, array $remoteJobTypes): ?string
    {
        $remoteJob = RemoteJob::query()
            ->select(['remote_job_type_id', 'reference_id', 'callback_status'])
            ->where('reference_id', $installationId)
            ->whereHas('remoteJobType', function ($query) use ($remoteJobTypes) {
                $query->whereIn('key', $remoteJobTypes);
            })
            ->latest()->first();

        return $remoteJob && $remoteJob?->callback_status ? $remoteJob?->callback_status  : null;
    }
}
