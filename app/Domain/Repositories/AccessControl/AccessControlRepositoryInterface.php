<?php

namespace App\Domain\Repositories\AccessControl;

interface AccessControlRepositoryInterface
{
    public function getRolePermissions($roleId);
    public function createRolePermissions($roleId);
    public function updateRolePermission($request);
    public function readPermission($request);
    public function createPermission($request);
    public function updatePermission($request, $model);
    public function readRole($request);
    public function readModule($request);
    public function createModule($request);
    public function updateModule($request, $model);
}
