<?php

namespace App\Application\Controllers;

use App\Application\Requests\InstallationStoreRequest;
use App\Application\Requests\InstallationEditRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;

class InstallationController extends AppController
{
    /**
     * @return JsonResponse
     */
    public function getFormCreate(Request $request)
    {
        $customerId = $request->get('customer_id');
        $installationId = $request->get('installation_id');

        if ($installationId) {
            $installation = $this->appService::installation()->getInstallation($installationId);
            if ($installation) {
                $customerId = $installation['customer_id'];
            }
        }

        return $this->sendResponse($this->appService::installation()->getFormCreate($customerId));
    }

    /**
     * @param InstallationStoreRequest $request
     * @return JsonResponse
     */
    public function storeInstallation(InstallationStoreRequest $request)
    {
        $params = $request->all();
        $params['installation_type'] = "standard";
        $this->appService::installation()->storeInstallation($params);
        return $this->sendResponse([], SAVE_SUCCESS);
    }

    /**
     * @param InstallationStoreRequest $request
     * @return JsonResponse
     */
    public function editInstallation(InstallationEditRequest $request)
    {
        $params = $request->all();
        $this->appService::installation()->editInstallation($params);
        return $this->sendResponse([], SAVE_SUCCESS);
    }

        /**
     * @param InstallationStoreRequest $request
     * @return JsonResponse
     */
    public function getInstallation($id)
    {
        $result = $this->appService::installation()->getInstallation($id);
        return $this->sendResponse($result, DATA_RETRIEVE_SUCCESS);
    }

    /**
     * @return JsonResponse
     */
    public function listInstallations()
    {
        $filter = request()->get('filter');
        $page = request()->get('page');
        $perPage = request()->get('per_page');
        $sortBy = request()->get('sort_by');
        $sortDesc = request()->get('sort_desc');

        $result = $this->appService::installation()->listInstallations([
            'filter' => $filter,
            'page' => $page,
            'per_page' => $perPage,
            'sort_by' => $sortBy,
            'sort_desc' => $sortDesc
        ], getNonAdminCustomerId());

        return $this->sendResponse($result, DATA_RETRIEVE_SUCCESS);
    }

    public function listInstallationsByStatus($status)
    {
        return $this->sendResponse($this->appService::installation()->listInstallationsByStatus($status, auth()->user()->customer_id));
    }


    /**
     * @return JsonResponse
     */
    public function validateDomain()
    {
        $domain = request()->get('domain');
        if (checkdnsrr($domain, 'ANY')) {
            return response()->json(['isTaken' => true]);
        } else {
            return response()->json(['isTaken' => false]);
        }
    }

    /**
     * @return JsonResponse
     */
    public function deleteInstallations()
    {
        $id = request()->get('id');

        $result = $this->appService::installation()->deleteInstallations($id);

        return $this->sendResponse($result, $result);
    }

    /**
     * @param InstallationStoreRequest $request
     * @return JsonResponse
     */
    public function getInstallationForManage($id)
    {
        $result = $this->appService::installation()->getInstallationForManage(['id' => $id]);
        return $this->sendResponse($result, DATA_RETRIEVE_SUCCESS);
    }

    /**
     * @param InstallationStoreRequest $request
     * @return JsonResponse
     */
    public function buildInstallation(InstallationStoreRequest $request)
    {
        return $this->sendResponse(null, DATA_RETRIEVE_SUCCESS);
    }

}
