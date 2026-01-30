<?php

namespace App\Domain\Services\Webhook;

interface AlertProcessorServiceInterface
{
    public function ansibleCallback($params);
}
