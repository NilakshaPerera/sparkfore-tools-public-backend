<?php

namespace App\Domain\Repositories\User;

interface UserRepositoryInterface
{
    /**
     * @param $id
     * @return mixed
     */
    public function getOne($id);
    public function readUser($request);
    public function createUser($request);
    public function updateUser($request, $user);
    public function updateProfile($request);
    public function readCompanies($request);
    public function readAccountTypes($request);
}
