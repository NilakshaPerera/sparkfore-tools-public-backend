<?php

namespace app\Domain\Repositories\AccessControl;

use App\Domain\Models\Module;
use App\Domain\Models\Permission;
use App\Domain\Models\Role;
use App\Domain\Models\RoleHasPermission;
use App\Domain\Models\User;
use App\Domain\Repositories\AccessControl\AccessControlRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AccessControlRepository implements AccessControlRepositoryInterface
{
    // User permission model inject here ...
    public function __construct(
        User $user,
        Module $module,
        Permission $permission,
        Role $role,
        RoleHasPermission $roleHasPermission
        )
    {
        $this->user = $user;
        $this->module = $module;
        $this->permission = $permission;
        $this->role = $role;
        $this->roleHasPermission = $roleHasPermission;
    }

    /**
     * @param $userId
     * @return array
     */
    public function getRolePermissions($roleId)
    {
        return getRolePermissions($roleId);
    }

    /**
     * Undocumented function
     *
     * @param [type] $roleId
     * @return array
     */
    public function createRolePermissions($roleId)
    {
        return [];
    }

    public function updateRolePermission($request)
    {
        DB::beginTransaction();
        try {
            RoleHasPermission::truncate();

            foreach ($request['roleperms'] as $permission => $roles) {

                // Always add admin role id
                if (!in_array(1, $roles)) {
                    array_push($roles, 1);
                }

                if ($permission && $roles) {



                    foreach ($roles as $role) {
                        RoleHasPermission::create(
                            array(
                                'role_id' => $role,
                                'status' => 'allowed',
                                'permission_id' => $permission,
                            )
                        );
                    }

                }
            }
            DB::commit();
        } catch (\Exception $e) {
            Log::error($e);
            DB::rollBack();
        }
        return [];
    }

    public function readPermission($request)
    {
        return Permission::with(['module', 'roleHasPermission'])->get();
    }

    /**
     * Undocumented function
     *
     * @param [type] $request
     * @return array
     */
    public function createPermission($request)
    {
        return Permission::create([
            'module_id' => $request->module,
            'name' => $request->name,
            'codename' => createCodeName($request->module, $request->name, $request->action),
            'description' => $request->description,
            'action' => $request->action,
        ]);

    }

    /**
     * Undocumented function
     *
     * @param [type] $request
     * @param [type] $model
     * @return array
     */
    public function updatePermission($request, $model)
    {
        $model->module_id = $request->module;
        $model->name = $request->name;
        $model->codename = createCodeName($request->module, $request->name, $request->action);
        $model->description = $request->description;
        $model->action = $request->action;

        $model->save();
        return $model;
    }

    public function readRole($request)
    {
        return Role::where('name', '!=', 'admin')->get();
    }

    public function readModule($request)
    {
        return Module::all();
    }

    /**
     * Undocumented function
     *
     * @param [type] $request
     * @return array
     */
    public function createModule($request)
    {
        return Module::create([
            'name' => $request->name
        ]);
    }

    /**
     * Undocumented function
     *
     * @param [type] $request
     * @param [type] $model
     * @return array
     */
    public function updateModule($request, $model)
    {
        $model->name = $request->name;
        $model->save();
        return $model;
    }
}
