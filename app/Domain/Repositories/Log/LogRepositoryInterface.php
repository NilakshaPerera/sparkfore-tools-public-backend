<?php

namespace App\Domain\Repositories\Log;

interface LogRepositoryInterface
{
    public function createLog($entry, $type, $userId = null);
}
