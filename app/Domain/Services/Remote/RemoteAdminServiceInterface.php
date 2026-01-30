<?php

namespace App\Domain\Services\Remote;

interface RemoteAdminServiceInterface
{
    public function restartServer($params);

    public function deletePipeline($params);

    public function createpipeline(array $params): array;

    public function getRemoteLogs($product, $jobTypeId, $env, $page, $perPage, $sortBy, $sortDesc, $ccustomerId=null);

    public function downloadRemoteJobFile($remoteJob, $fileType);
}
