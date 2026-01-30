<?php

namespace App\Domain\DataClasses\Installation;

use Illuminate\Support\Str;

class SetupFreeInstallationDto extends DeleteInstallationDto
{
    protected $customerSlug;
    protected $baseProduct;
    protected $registryNamespace;
    protected $productPackage;

    public function setFromInstallation($installationModel) {
        parent::setFromInstallation($installationModel);

        $this->customerSlug = $installationModel->customerProduct->customer->slugified_name;
        $this->baseProduct = Str::slug($installationModel->customerProduct->product->productSoftwares->first()->software->name);
        if($installationModel->customerProduct->product->legacy_product_name != null || $installationModel->customerProduct->product->legacy_product_name != "") {
            $this->productPackage = $installationModel->customerProduct->product->legacy_product_name;
        } else {
            $this->productPackage = Str::slug($installationModel->customerProduct->product->pipeline_name);
        }
        $this->registryNamespace = getProductNameSpace($installationModel->customerProduct->product);
    }

    public function setCustomerSlug($customerSlug)
    {
        $this->customerSlug = $customerSlug;
    }

    public function setBaseProduct($baseProduct)
    {
        $this->baseProduct = $baseProduct;
    }
}
