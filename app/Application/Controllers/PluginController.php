<?php

namespace App\Application\Controllers;

use App\Application\Requests\StorePluginRequest;
use App\Domain\Models\Plugin;
use Illuminate\Http\JsonResponse;

class PluginController extends AppController
{
    /**
     * @return JsonResponse
     */
    public function listPlugins()
    {
        $filter = request()->get('filter');
        $page = request()->get('page');
        $perPage = request()->get('per_page');
        $sortBy = request()->get('sort_by');
        $sortDesc = request()->get('sort_desc');

        $result = $this->appService::plugin()->listPlugins([
            'filter' => $filter,
            'page' => $page,
            'per_page' => $perPage,
            'sort_by' => $sortBy,
            'sort_desc' => $sortDesc
        ], getNonAdminCustomerId());

        return $this->sendResponse($result, DATA_RETRIEVE_SUCCESS);
    }

    /**
     * @return JsonResponse
     */
    public function getSoftwarePlugins()
    {
        $sortBy = request()->get('sort_by');
        $sortDesc = request()->get('sort_desc');
        $filter = request()->get('filter');
        $softwareId = request()->get('software_id');
        $versionType = request()->get('version_type');
        $supportedVersion = request()->get('supported_version');
        $pluginId = request()->get('plugin', null);

        return $this->sendResponse($this->appService::plugin()->getSoftwarePlugins([
            'sort_by' => $sortBy,
            'sort_desc' => $sortDesc,
            'filter' => $filter,
            'software_id' => $softwareId,
            'version_type' => $versionType,
            'supported_version' => $supportedVersion,
            'pluginId' => $pluginId
        ], getNonAdminCustomerId()));
    }

    /**
     * @return JsonResponse
     */
    public function getFormCreate()
    {
        return $this->sendResponse($this->appService::plugin()->getFormCreate());
    }

    /**
     * @param StorePluginRequest $request
     * @return JsonResponse
     */
    public function storePlugin(StorePluginRequest $request)
    {
        $params = $request->all();
        $this->appService::plugin()->storePlugin($params);
        return $this->sendResponse([], SAVE_SUCCESS);
    }

    /**
     * @param $id
     * @param StorePluginRequest $request
     * @return JsonResponse
     */
    public function updatePlugin($id, StorePluginRequest $request)
    {
        $params = $request->all();
        $this->appService::plugin()->updatePlugin($params);
        return $this->sendResponse([], SAVE_SUCCESS);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        return $this->appService::plugin()->edit($id);
    }

        /**
     * @param $id
     * @return mixed
     */
    public function getGitPluginName()
    {
        $url = request()->get('url');
        $token = request()->get('token');
        return $this->appService::plugin()->getGitPluginName($url, $token);
    }

            /**
     * @param $id
     * @return mixed
     */
    public function getPluginVersions($id)
    {
        return $this->appService::plugin()->getPluginVersions($id, [
            "moodleVersion" => request()->get("supported_version", null),
            "softwareId" => request()->get("software_id", null),
            "softwareVersionType" => request()->get("version_type", null)
        ]);
    }

    public function syncPlugin(Plugin $plugin)
    {
        return $this->sendResponse([], $this->appService::plugin()->syncPlugin($plugin));
    }

    public function getPluginByURL($gitUrl){
        $gitUrl = base64_decode($gitUrl);
        return $this->sendResponse(data: $this->appService::plugin()->getPluginByURL($gitUrl));
    }
}
