<?php

namespace App\Domain\Services\User;

use App\Domain\Exception\SparkforeException;
use App\Domain\Repositories\User\UserRepositoryInterface;
use App\Domain\Services\User\UserServiceInterface;
use Illuminate\Support\Facades\Hash;
use Log;

class UserService implements UserServiceInterface
{
    protected $repository;

    public function __construct(UserRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getOne($id)
    {
        $user = $this->repository->getOne($id);
        return [
            'user' => collect($user)->except([PASSWORD]),
            'permissions' => auth()->user()->role->permissions->pluck('codename')
        ];
    }

    public function readUser($request)
    {
        return $this->repository->readUser($request);
    }

    public function createUser($request)
    {
        return $this->repository->createUser($request);
    }

    public function readAccountTypes($request)
    {
        return $this->repository->readAccountTypes($request);
    }

    public function readCompanies($request)
    {
        return $this->repository->readCompanies($request);
    }

    public function updateUser($request, $user)
    {

        $this->validateLoggedInUserPass($request);
        return $this->repository->updateUser($request, $user);
    }

    public function updateProfile($request)
    {
        $this->validateLoggedInUserPass($request);
        return $this->repository->updateProfile($request);
    }

    /**
     * if you are using this function remember to add necessary precautions to stop
     * password guessing from brute force attack
     */
    private function validateLoggedInUserPass($request)
    {
        $loggedInUser = auth()->user();
        if (!Hash::check($request->input('current_password'), $loggedInUser->password)) {
            Log::warning('Failed password attempt for user ID: ' . $loggedInUser->id);
            throw new SparkforeException("Password of logged in user is incorrect", 422);
        }
    }
}
