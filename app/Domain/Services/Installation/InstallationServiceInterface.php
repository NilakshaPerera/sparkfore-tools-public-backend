<?php

namespace App\Domain\Services\Installation;

interface InstallationServiceInterface
{
    public function editInstallation($params);
    public function storeInstallation($params);
}
