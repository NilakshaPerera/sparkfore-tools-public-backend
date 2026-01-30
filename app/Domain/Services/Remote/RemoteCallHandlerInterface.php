<?php

namespace App\Domain\Services\Remote;

use App\Domain\DataClasses\Installation\ChangeInstallationDiskSizeDto;
use App\Domain\DataClasses\Installation\DeleteInstallationDto;
use App\Domain\DataClasses\Installation\SetupFreeInstallationDto;
use App\Domain\DataClasses\Installation\SetupInstallationDto;

interface RemoteCallHandlerInterface
{

    public function createPipeline(
        $jobId, $customer, $customerSlug, $baseProduct, $baseProductSlug, $name, $nameSlug, $legacy
    );
    public function buildPipeline(
        $jobId,
        $customer,
        $customerSlug,
        $baseProduct,
        $baseProductSlug,
        $name,
        $nameSlug,
        $branch,
        $buildVersion,
        $legacy
    );
    public function deletePipeline($jobId, $customer, $customerSlug, $baseProduct, $baseProductSlug, $name, $nameSlug);

    public function createCustomer($jobId, $customer, $customerSlug);

    public function renameCustomer($jobId, $oldCustomer, $oldCustomerSlug, $newCustomer, $newCustomerSlug);
    public function setupInstallation(SetupInstallationDto $setupInstallationDto);
    public function setupFreeInstallation(SetupFreeInstallationDto $setupFreeInstallationDto);
    public function deleteInstallation(DeleteInstallationDto $deleteInstallationDto);
    public function changeInstallationDiskSize(ChangeInstallationDiskSizeDto $changeInstallationDiskSizeDto);
}
