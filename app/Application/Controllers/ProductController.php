<?php

namespace App\Application\Controllers;

use App\Application\Requests\CustomerProductStoreRequest;
use App\Domain\Models\PipelineMaintainer;
use App\Domain\Models\Product;
use Avency\Gitea\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends AppController
{
    /**
     * @return JsonResponse
     */
    public function getFormCreate()
    {
        return $this->sendResponse($this->appService::product()->getFormCreate());
    }

    /**
     * @return JsonResponse
     */
    public function getFormSettingsCreate($env, $id)
    {
        return $this->sendResponse($this->appService::product()->getFormSettingsCreate($env, $id));
    }

    /**
     * @return JsonResponse
     */
    public function getFormSettingsData($env, $id)
    {
        $params = [
            "moodleVersion" => request()->get("moodleVersion", null),
            "moodleVersionType" => request()->get("moodleVersionType", null)
        ];
        return $this->sendResponse($this->appService::product()->getFormSettingsData($env, $id, $params));
    }

    public function getFormSettingsPluginsData()
    {
        $id = request()->get("productId");
        $env = request()->get("env");
        $versionType = request()->get("moodleVersionType");
        $softwareId = request()->get("softwareId");
        $supportedVersion = request()->get("moodleVersion");

        return $this->sendResponse(
            $this->appService::product()
                ->getFormSettingsPluginsData($id, $env, $versionType, $softwareId, $supportedVersion)
        );
    }



    /**
     * @return JsonResponse
     */
    public function getCustomerProductFormCreate()
    {
        return $this->sendResponse($this->appService::product()->getCustomerProductFormCreate());
    }

    /**
     * @param CustomerProductStoreRequest $request
     * @return JsonResponse
     */
    public function editCustomerProduct(CustomerProductStoreRequest $request)
    {
        $params = $request->all();
        return $this->sendResponse($this->appService::product()->editCustomerProduct($params));
    }

    /**
     * @param CustomerProductStoreRequest $request
     * @return JsonResponse
     */
    public function getCustomerProduct()
    {
        $productId = request()->get('productId');
        $customerId = request()->get('customerId');
        return $this->sendResponse($this->appService::product()->getCustomerProduct($productId, $customerId));
    }

    /**
     * @return JsonResponse
     */
    public function listProducts()
    {
        $filter = request()->get('filter');
        $page = request()->get('page');
        $perPage = request()->get('per_page');
        $sortBy = request()->get('sort_by');
        $sortDesc = request()->get('sort_desc');

        $result = $this->appService::product()->listProducts([
            'filter' => $filter,
            'page' => $page,
            'per_page' => $perPage,
            'sort_by' => $sortBy,
            'sort_desc' => $sortDesc
        ], getNonAdminCustomerId());

        return $this->sendResponse($result, DATA_RETRIEVE_SUCCESS);
    }

    public function storeProduct()
    {
        $params = request()->all();
        if(getNonAdminCustomerId()) {
            $params['customer'] = getNonAdminCustomerId();
            $params['availability'] = "private";
            $params['maintainer_id'] = PipelineMaintainer::where('name', 'Customer')->first()->id;
        }

        return $this->sendResponse($this->appService::product()->storeProduct($params));
    }

    public function getChangeHistory($product)
    {
        $page = request()->get('page');
        $perPage = request()->get('per_page');
        $env = request()->get('env');
        $sortBy = request()->get('sortBy');
        $sortDesc = request()->get('sortDesc');

        $result = $this->appService::product()->getChangeHistory($product, $env, $page, $perPage, $sortBy, $sortDesc);

        return $this->sendResponse($result, DATA_RETRIEVE_SUCCESS);
    }

    public function updateProduct($id)
    {
        $params = request()->all();
        $params['user_id'] = Auth::user()->id;
        return $this->sendResponse(
            [],
            $this->appService::product()->updateProduct($id, $params)
        );
    }

    public function updateProductEnvironmentPlugins($id)
    {
        $params = request()->all();
        return $this->sendResponse($this->appService::product()->updateProductEnvironmentPlugins($id, $params));
    }

    public function test()
    {
        $cient = new Client(
            'https://git.autotech.se',
            [
                'type' => Client::AUTH_TOKEN,
                'auth' => 'eb50c5a396f804ae0cf1f99f4f99cb60249dcf03'
            ]
        );

        // Get a single repository
        $repository = $cient->repositories()->getTags('LMS-Mirror', 'moodle');

        dd($repository);
    }

    /**
     *  Check if product name is available
     *
     * @return JsonResponse
     */
    public function isProductNameAvailable()
    {
        $pipelineName = request()->query('name');
        $isAvailable = $this->appService::product()->isProductNameAvailable($pipelineName);
        return $this->sendResponse($isAvailable);
    }

    public function triggerBuildPipelineRequest(Product $product, Request $request)
    {
        $rules = [
            'environment' => 'required|in:develop,staging,production'
        ];

        $validation = validator(
            $request->toArray(),
            $rules
        );

        if ($validation->fails()) {
            return $this->sendErrorResponse($validation->errors(), VALIDATION_ERROR, 422);
        }

        $product->load('productSoftwares.software', 'productCustomer.customer');

        $this->appService::product()->triggerBuildPipelineRequest($product, $request->get('environment'));
        return $this->sendResponse("Build pipeline request has been triggered successfully");
    }

    public function syncPluginsFromGit(Product $product, Request $request)
    {
        $rules = [
            'env' => 'required|in:dev,develop,staging,production'
        ];

        $validation = validator(
            $request->toArray(),
            $rules
        );

        if ($validation->fails()) {
            return $this->sendErrorResponse($validation->errors(), VALIDATION_ERROR, 422);
        }

        if ($request->get('environment') == "dev") {
            $request->merge(['environment' => 'develop']);
        }

        $this->appService::product()->syncPluginsFromGit($product);
        return $this->sendResponse([], "Plugin sync in progress. Refresh the page for updates");
    }

    public function deleteProductModule(Product $product)
    {
        return $this->sendResponse(
            $this->appService::product()->deleteProductModule($product, request()->get("module"))
        );
    }
}
