<?php

namespace App\Application\Controllers;

use App\Application\Requests\StoreSoftwareRequest;
use App\Domain\Jobs\SyncSoftwaresJob;
use Illuminate\Http\JsonResponse;

class SoftwareController extends AppController
{
    /**
     * @return JsonResponse
     */
    public function listSoftware()
    {
        $filter = request()->get('filter');
        $page = request()->get('page');
        $perPage = request()->get('per_page');
        $sortBy = request()->get('sort_by');
        $sortDesc = request()->get('sort_desc');

        $result = $this->appService::software()->listSoftware([
            'filter' => $filter,
            'page' => $page,
            'per_page' => $perPage,
            'sort_by' => $sortBy,
            'sort_desc' => $sortDesc
        ]);

        return $this->sendResponse($result, DATA_RETRIEVE_SUCCESS);
    }

    /**
     * @return JsonResponse
     */
    public function getFormCreate()
    {
        return $this->sendResponse($this->appService::software()->getFormCreate());
    }

        /**
     * @return JsonResponse
     */
    public function getSoftwareVersions()
    {
        $gitUrl = request()->get('git_url');
        $typeId = request()->get('git_version_type_id');
        $result = $this->appService::software()->getSoftwareVersions($gitUrl, $typeId);
        return $this->sendResponse($result, DATA_RETRIEVE_SUCCESS);
    }


    /**
     * @param StoreSoftwareRequest $request
     * @return JsonResponse
     */
    public function storeSoftware(StoreSoftwareRequest $request)
    {
        $params = $request->all();
        $this->appService::software()->storeSoftware($params);
        return $this->sendResponse([], SAVE_SUCCESS);
    }

    public function updateSoftware($id, StoreSoftwareRequest $request)
    {
        $params = $request->all();
        $this->appService::software()->updateSoftware($params);
        return $this->sendResponse([], SAVE_SUCCESS);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        return $this->appService::software()->edit($id);
    }

    public function syncSoftwares()
    {
        return $this->sendResponse([], $this->appService::software()->syncSoftwares());
    }
}
