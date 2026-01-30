<?php

namespace App\Domain\Services\ServiceApi;

use App\Domain\DataClasses\Moodle\MoodleUpdateUserDto;

interface MoodleApiServiceInterface
{

    public function coreUserUpdateUser(MoodleUpdateUserDto $userDto);

}
