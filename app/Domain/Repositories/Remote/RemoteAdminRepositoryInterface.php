<?php

namespace App\Domain\Repositories\Remote;

interface RemoteAdminRepositoryInterface
{
    public function updateRemoteJob($jobId, $params);
    public function createRemoteJob($jobType, $userId, $referenceId = null, $branch = null);
    public function getRemoteLogs($product, $jobTypeId, $env, $page, $perPage, $sortBy, $sortDesc, $customerId = null);
}
