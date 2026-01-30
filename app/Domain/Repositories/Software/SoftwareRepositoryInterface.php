<?php

namespace App\Domain\Repositories\Software;

interface SoftwareRepositoryInterface
{

    public function getSoftware($id);

    public function edit($id);

    public function getSoftwareByName($name);

    public function getSoftwareVersions($softwareId, $versionType);

    public function getLatestSoftwareVersions($softwareId, $versionType, $currentVersionId);

    public function getSoftwareById($id);
}
