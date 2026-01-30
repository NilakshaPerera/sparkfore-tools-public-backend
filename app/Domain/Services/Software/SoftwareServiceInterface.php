<?php

namespace App\Domain\Services\Software;

interface SoftwareServiceInterface
{
    public function getSoftwareVersions($gitUrl, $typeId);

}
