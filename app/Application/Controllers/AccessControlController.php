<?php

namespace App\Application\Controllers;

use App\Application\Requests\AccessControl\CreateModuleRequest;
use App\Application\Requests\AccessControl\CreatePermission;
use App\Application\Requests\AccessControl\CreateRolePermissionsRequest;
use App\Application\Requests\AccessControl\GetRolePermissionsRequest;
use App\Application\Requests\AccessControl\ReadModule;
use App\Application\Requests\AccessControl\ReadPermission;
use App\Application\Requests\AccessControl\ReadRole;
use App\Application\Requests\AccessControl\UpdateModuleRequest;
use App\Application\Requests\AccessControl\UpdatePermission;
use App\Application\Requests\AccessControl\UpdateRolePermission;
use App\Domain\Models\Module;
use App\Domain\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class AccessControlController extends AppController
{
    /**
     * @return JsonResponse
     */
    public function getRolePermissions(GetRolePermissionsRequest $request)
    {
        $user = Auth::user();
        return $this->sendResponse($this->appService->accessControl()->getRolePermissions($user));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function createRolePermissions(CreateRolePermissionsRequest $request)
    {
        $user = Auth::user();
        return $this->sendResponse($this->appService->accessControl()->createRolePermissions($user));
    }

    public function updateRolePermission(UpdateRolePermission $request)
    {
        return $this->sendResponse($this->appService->accessControl()->updateRolePermission($request));
    }

    public function readPermission(ReadPermission $request)
    {
        return $this->sendResponse($this->appService->accessControl()->readPermission($request));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function createPermission(CreatePermission $request)
    {
        return $this->sendResponse($this->appService->accessControl()->createPermission($request));
    }

    /**
     * @param Request $request
     * @param Permission $model
     * @return JsonResponse
     */
    public function updatePermission(UpdatePermission $request, Permission $model)
    {
        return $this->sendResponse($this->appService->accessControl()->updatePermission($request, $model));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function readRole(ReadRole $request)
    {
        return $this->sendResponse($this->appService->accessControl()->readRole($request));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function readModule(ReadModule $request)
    {
        return $this->sendResponse($this->appService->accessControl()->readModule($request));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function createModule(CreateModuleRequest $request)
    {
        return $this->sendResponse($this->appService->accessControl()->createModule($request));
    }

    /**
     * @param Request $request
     * @param Module $model
     * @return JsonResponse
     */
    public function updateModule(UpdateModuleRequest $request, Module $model)
    {
        return $this->sendResponse($this->appService->accessControl()->updateModule($request, $model));
    }
}
