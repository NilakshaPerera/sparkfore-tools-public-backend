<?php

namespace App\Domain\Services\AccessControl;

interface AccessControlInterface
{
    public function getRolePermissions($role);

    public function createRolePermissions($role);

    public function updateRolePermission($request);

    public function readPermission($request);

    public function createPermission($request);

    public function updatePermission($request, $model);

    public function readRole($request);

    public function readModule($request);

    public function createModule($request);

    public function updateModule($request, $model);
}
