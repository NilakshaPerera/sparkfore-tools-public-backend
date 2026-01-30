<?php

namespace App\Domain\DataClasses\Installation;

use App\Domain\Exception\SparkforeException;
use App\Domain\Models\Installation;

class SetupInstallationDto extends SetupFreeInstallationDto
{
    protected $deploymentSize;
    protected $pipeline;
    protected $branch;
    protected $version;

    public function __construct(Installation $installationModel)
    {
        $this->setFromInstallation($installationModel);
    }

    public function setFromInstallation($installationModel) {
        parent::setFromInstallation($installationModel);
        $this->deploymentSize = $installationModel->hosting->basePackage->ansible_package_id;
        $this->pipeline = $installationModel->customerProduct->product->pipeline_name;

        $branchValue = isset($installationModel->targetType->key) ? $installationModel->targetType->key : 'develop';
        if (strpos($branchValue, 'development') !== false) {
            $this->branch = str_replace('development', 'develop', $branchValue);
        } else {
            $this->branch = $branchValue;
        }

        $this->version = $this->getSoftwareVersion($installationModel);
    }


    public function setPipeline($pipeline)
    {
        $this->pipeline = $pipeline;
    }

    private function getSoftwareVersion($installationModel)
    {
        $proSoftware = $installationModel->customerProduct->product->productSoftwares->first();

        if ($proSoftware->supported_version_type == 2) {
            preg_match('/v(\d+)\.(\d+)\.(\d+)/', $proSoftware->supported_version, $matches);
            $majorVersion = $matches[1];
            $minorVersion = $matches[2];
            return $majorVersion . $minorVersion;
        }
        return $proSoftware->supported_version;
    }
}
