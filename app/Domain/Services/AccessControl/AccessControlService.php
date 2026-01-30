<?php

namespace App\Domain\Services\AccessControl;

use App\Domain\Services\AccessControl\AccessControlInterface;
use App\Domain\Repositories\AccessControl\AccessControlRepositoryInterface;

class AccessControlService implements AccessControlInterface
{
    protected  $repository;

    public function __construct(AccessControlRepositoryInterface $acRepository)
    {
        $this->repository = $acRepository;
    }

    public function getRolePermissions($role)
    {
        $roleId = $role->id;
        $this->repository->getRolePermissions($roleId);
    }

    public function createRolePermissions($role)
    {
        $role = $role->id;
        $this->repository->createRolePermissions($role);
    }

    public function updateRolePermission($request)
    {
        return $this->repository->updateRolePermission($request);
    }

    public function readPermission($request)
    {
        return $this->repository->readPermission($request);
    }

    public function createPermission($request)
    {
        $this->repository->createPermission($request);
    }

    public function updatePermission($request, $model)
    {
        $this->repository->updatePermission($request, $model);
    }

    public function readRole($request)
    {
        return $this->repository->readRole($request);
    }

    public function readModule($request)
    {
        return $this->repository->readModule($request);
    }

    public function createModule($request)
    {
        return $this->repository->createModule($request);
    }

    public function updateModule($request, $model)
    {
        $this->repository->updateModule($request, $model);
    }
}
