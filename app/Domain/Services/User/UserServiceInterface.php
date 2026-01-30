<?php

namespace App\Domain\Services\User;

interface UserServiceInterface
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

    public function readAccountTypes($request);

    public function readCompanies($request);
}
