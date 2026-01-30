<?php

namespace App\Domain\Services\User;

use App\Domain\Exception\SparkforeException;
use App\Domain\Repositories\Log\LogRepositoryInterface;
use Log;

class LogService implements LogServiceInterface
{
    protected $logRepository;

    public function __construct(LogRepositoryInterface $logRepositoryInterface)
    {
        $this->logRepository = $logRepositoryInterface;
    }


}
