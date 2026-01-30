<?php

namespace App\Application\Controllers;

use App\Application\Requests\User\CreateUserRequest;
use App\Application\Requests\User\ReadAccountTypeRequest;
use App\Application\Requests\User\ReadCompaniesRequest;
use App\Application\Requests\User\ReadUserRequest;
use App\Application\Requests\User\UpdateProfileRequest;
use App\Application\Requests\User\UpdateUserRequest;
use App\Domain\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends AppController
{
    /**
     * @return mixed
     */
    public function authUser()
    {
        $user = Auth::user();
        return $this->sendResponse($this->appService::user()->getOne($user->id));
    }

    /**
     * Created By : Nilaksha
     * Created At : 02 /11/2023
     * Summary  : Read all users
     *
     * @param readUserRequest $request
     * @return void
     */
    public function readUser(ReadUserRequest $request)
    {
        return $this->sendResponse($this->appService->user()->readUser($request));
    }

    public function createUser(CreateUserRequest $request)
    {
        return $this->sendResponse($this->appService->user()->createUser($request));
    }


    public function readAccountTypes(ReadAccountTypeRequest $request)
    {
        return $this->sendResponse($this->appService->user()->readAccountTypes($request));
    }

    public function readCompanies(ReadCompaniesRequest $request)
    {
        return $this->sendResponse($this->appService->user()->readCompanies($request));
    }

    /**
     * Creaed By : Nilaksha
     * Created At : 02/11/2023
     * Summary : Update user
     *
     * @param UpdateUserRequest $request
     * @param User $user
     * @return void
     */
    public function updateUser(UpdateUserRequest $request, User $user)
    {
        return $this->sendResponse($this->appService->user()->updateUser($request, $user));
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        return $this->sendResponse($this->appService->user()->updateProfile($request));
    }
}
