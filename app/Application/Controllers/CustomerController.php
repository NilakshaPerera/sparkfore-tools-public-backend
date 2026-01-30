<?php

namespace App\Application\Controllers;

use App\Application\Requests\CustomerStoreRequest;
use Illuminate\Http\JsonResponse;

class CustomerController extends AppController
{

    /**
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        return $this->appService::customer()->edit($id);
    }

    public function updateCustomer($id, CustomerStoreRequest $request)
    {
        $params = $request->all();
        $this->appService::customer()->updateCustomer($params);
        return $this->sendResponse([], SAVE_SUCCESS);
    }

    /**
     * @param CustomerStoreRequest $request
     * @return JsonResponse
     */
    public function storeCustomer(CustomerStoreRequest $request)
    {
        $data = $request->post();
        $this->appService->customer()->storeCustomer($data);
        return $this->sendResponse([], SAVE_SUCCESS);
    }

    /**
     * @return JsonResponse
     */
    public function listCustomers()
    {
        $filter = request()->get('filter');
        $page = request()->get('page');
        $perPage = request()->get('per_page');

        $result = $this->appService::customer()->listCustomers([
            'filter' => $filter,
            'page' => $page,
            'per_page' => $perPage
        ]);

        return $this->sendResponse($result, DATA_RETRIEVE_SUCCESS);
    }

    /**
     * @return JsonResponse
     */
    public function listCustomerProducts()
    {
        $filter = request()->get('filter');
        $id = request()->get('id');

        if (auth()->user()->role->name != 'admin' && getNonAdminCustomerId() != $id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $result = $this->appService::customer()->listCustomerProducts([
            'filter' => $filter,
            'id' => $id
        ], getNonAdminCustomerId());

        return $this->sendResponse($result, DATA_RETRIEVE_SUCCESS);
    }
}
