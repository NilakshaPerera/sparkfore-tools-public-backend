<?php

namespace App\Domain\Repositories\Installation;

interface InstallationRepositoryInterface
{
    public function deleteInstallations($installationId);

    public function listInstallationsByStatus($status, $customerId=null);

    public function getInstallation($id);

    public function editInstallation($data);

    public function getCustomerProductId($customerId, $productId);

    public function getInstallationForManage($id);

}
