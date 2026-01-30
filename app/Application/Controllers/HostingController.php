<?php

namespace App\Application\Controllers;

use App\Application\Requests\StoreHostingRequest;
use Illuminate\Http\JsonResponse;

class HostingController extends AppController
{
    /**
     * @return JsonResponse
     */
    public function listHosting()
    {
        $filter = request()->get('filter');
        $page = request()->get('page');
        $perPage = request()->get('per_page');
        $sortBy = request()->get('sort_by');
        $sortDesc = request()->get('sort_desc');


        $result = $this->appService::hosting()->listHosting([
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
        return $this->sendResponse($this->appService::hosting()->getFormCreate());
    }

    /**
     * @param StoreHostingRequest $request
     * @return JsonResponse
     */
    public function storeHosting(StoreHostingRequest $request)
    {
        $params = $request->all();
        $this->appService::hosting()->storeHosting($params);
        return $this->sendResponse([], SAVE_SUCCESS);
    }

    public function updateHosting($id, StoreHostingRequest $request)
    {
        $params = $request->all();
        $this->appService::hosting()->updateHosting($params);
        return $this->sendResponse([], SAVE_SUCCESS);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        return $this->appService::hosting()->edit($id);
    }
}
