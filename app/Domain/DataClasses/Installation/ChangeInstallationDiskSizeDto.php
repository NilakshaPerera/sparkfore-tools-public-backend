<?php

namespace App\Domain\DataClasses\Installation;

use App\Domain\DataClasses\AppDataClass;

class ChangeInstallationDiskSizeDto extends SetupFreeInstallationDto
{
    protected $diskSize; // disk size in GB => 10
    protected $diskName = "moodledata";
    protected $diskId = 1;

    public function __construct($installationModel, $diskSize)
    {
        $this->setFromInstallation($installationModel);
        $this->diskSize = $diskSize;
    }

    public function getDiskSize()
    {
        return $this->diskSize;
    }

    public function setDiskSize($diskSize)
    {
        $this->diskSize = $diskSize;
    }
}
