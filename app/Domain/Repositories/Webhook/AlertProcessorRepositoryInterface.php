<?php

namespace App\Domain\Repositories\Webhook;

interface AlertProcessorRepositoryInterface
{
    public function updateInstallation($installationId, $state, $status);
}
