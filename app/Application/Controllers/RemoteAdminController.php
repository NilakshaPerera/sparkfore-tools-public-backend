<?php

namespace App\Application\Controllers;

use App\Domain\Models\RemoteJobType;
use Illuminate\Support\Facades\Auth;

class RemoteAdminController extends AppController
{
    public function restartServer()
    {
        $params = request()->all();
        $params['user_id'] = Auth::user()->id;
        $response = $this->appService::remoteAdmin()->restartServer($params);
        return $this->sendResponse($response);
    }

    public function productBuildLogs($product)
    {
        $page = request()->get('page');
        $perPage = request()->get('per_page');
        $env = request()->get('env');
        $sortBy = request()->get('sortBy');
        $sortDesc = request()->get('sortDesc');

        $jobTypeId = RemoteJobType::where("key", REMOTE_JOB_TYPE_BUILD_PIPELINE)->first()->id;
        $result = $this->appService::remoteAdmin()->getRemoteLogs(
            $product,
            $jobTypeId,
            $env,
            $page,
            $perPage,
            $sortBy,
            $sortDesc,
            getNonAdminCustomerId()
        );
        return $this->sendResponse($result, DATA_RETRIEVE_SUCCESS);
    }

    public function downloadRemoteJobFile($remoteJob, $fileType)
    {
        return $this->sendResponse($this->appService::remoteAdmin()->downloadRemoteJobFile($remoteJob, $fileType));
    }
}
