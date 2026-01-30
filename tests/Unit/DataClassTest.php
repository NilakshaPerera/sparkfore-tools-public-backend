<?php

namespace tests\Unit;

use App\Domain\DataClasses\Customer\Customer;
use App\Domain\DataClasses\CustomerProduct\CustomerProduct;
use App\Domain\DataClasses\Installation\Installation;
use App\Domain\DataClasses\Plugin\Plugin;
use App\Domain\DataClasses\Product\Product;
use App\Domain\DataClasses\ProductPlugin\ProductPlugin;
use App\Domain\DataClasses\ProductSoftware\ProductSoftware;
use Tests\TestCase;

class DataClassTest extends TestCase
{

    public function test_Customer()
    {
        $dataObj = (new Customer())
                    ->setId(1)
                    ->setName('name')
                    ->setSlugifiedName('slugified_name')
                    ->setOrganizationNo('organization_no')
                    ->setInvoiceType('invoice_type')
                    ->setInvoiceAddress('invoice_address')
                    ->setInvoiceEmail('invoice_email')
                    ->setInvoiceReference('invoice_reference')
                    ->setInvoiceAnnotation('invoice_annotation');

                    $this->assertEquals(1, $dataObj->getId());
                    $this->assertEquals('name', $dataObj->getName());
                    $this->assertEquals('slugified_name', $dataObj->getSlugifiedName());
                    $this->assertEquals('organization_no', $dataObj->getOrganizationNo());
                    $this->assertEquals('invoice_type', $dataObj->getInvoiceType());
                    $this->assertEquals('invoice_address', $dataObj->getInvoiceAddress());
                    $this->assertEquals('invoice_email', $dataObj->getInvoiceEmail());
                    $this->assertEquals('invoice_reference', $dataObj->getInvoiceReference());
                    $this->assertEquals('invoice_annotation', $dataObj->getInvoiceAnnotation());

    }

    public function test_CustomerProduct()
    {
        $dataObj =  (new CustomerProduct())
        ->setId('customer_product_id')
        ->setLabel('test1')
        ->setCustomerId('test2')
        ->setProductId('test3')
        ->setBasePriceIncreaseYearly('test4')
        ->setBasePricingMethod('test5')
        ->setBasePricePerUserIncreaseYearly('test6')
        ->setPerUserPricingMethod('test7')
        ->setIncludeMaintenance('include_maintenance');

                    $this->assertEquals('customer_product_id', $dataObj->getId());
                    $this->assertEquals('test1', $dataObj->getLabel());
                    $this->assertEquals('test2', $dataObj->getCustomerId());
                    $this->assertEquals('test3', $dataObj->getProductId());
                    $this->assertEquals('test4', $dataObj->getBasePriceIncreaseYearly());
                    $this->assertEquals('test5', $dataObj->getBasePricingMethod());
                    $this->assertEquals('test6', $dataObj->getBasePricePerUserIncreaseYearly());
                    $this->assertEquals('test7', $dataObj->getPerUserPricingMethod());
                    $this->assertEquals('include_maintenance', $dataObj->getIncludeMaintenance());

    }

    public function test_Installation()
    {
        $dataObj =  (new Installation())
        ->setCustomerProductId(12)
        ->setId(1)
        ->setInstallationTargetTypeId('domain_type')
        ->setHostingId("test_url")
        ->setDomainType('billing_terms')
        ->setDomain('general_terms')
        ->setSubDomain('include_backup')
        ->setUrl('include_staging')
        ->setIncludeBackup('hosting_package')
        ->setGeneralTermsAgreement("hosting")
        ->setBillingTermsAgreement('hosting_provider')
        ->setStatus('hosting_provider1')
        ->setHostingProviderId('hosting_provider2')
        ->setHostingTypeId('hosting_provider3')
        ->setIncludeStagingPackage(122);

                    $this->assertEquals(12, $dataObj->getCustomerProductId());
                    $this->assertEquals('1', $dataObj->getId());
                    $this->assertEquals('domain_type', $dataObj->getInstallationTargetTypeId());
                    $this->assertEquals('test_url', $dataObj->getHostingId());
                    $this->assertEquals('billing_terms', $dataObj->getDomainType());
                    $this->assertEquals('general_terms', $dataObj->getDomain());
                    $this->assertEquals('include_backup', $dataObj->getSubDomain());
                    $this->assertEquals('include_staging', $dataObj->getUrl());
                    $this->assertEquals('hosting_package', $dataObj->getIncludeBackup());
                    $this->assertEquals('hosting', $dataObj->getGeneralTermsAgreement());
                    $this->assertEquals('hosting_provider', $dataObj->getBillingTermsAgreement());
                    $this->assertEquals('hosting_provider1', $dataObj->getStatus());
                    $this->assertEquals('hosting_provider2', $dataObj->getHostingProviderId());
                    $this->assertEquals('hosting_provider3', $dataObj->getHostingTypeId());
                    $this->assertEquals(122, $dataObj->getIncludeStagingPackage());

    }


    public function test_Product()
    {
        $dataObj =  (new Product())
        ->setId('maintainer_id')
        ->setPipelineMaintainerId('product_name')
        ->setPipelineName('git_url')
        ->setReleaseNotes('release_notes')
        ->setPipelineBuildStatus('pipeline_build_status')
        ->setLastBuild('last_build')
        ->setDevelopmentScheduledBuild("test_val_2")
        ->setStagingScheduledBuild("test_val_1")
        ->setProductionScheduledBuild("test_val_3")
        ->setAvailability('availability')
        ->setGitUrl('hosting_provider2')
        ->setCreatedAt('hosting_provider1')
        ->setUpdatedAt('hosting_provider2');

                    $this->assertEquals('maintainer_id', $dataObj->getId());
                    $this->assertEquals('product_name', $dataObj->getPipelineMaintainerId());
                    $this->assertEquals('git_url', $dataObj->getPipelineName());
                    $this->assertEquals('release_notes', $dataObj->getReleaseNotes());
                    $this->assertEquals('pipeline_build_status', $dataObj->getPipelineBuildStatus());
                    $this->assertEquals('last_build', $dataObj->getLastBuild());
                    $this->assertEquals('test_val_2', $dataObj->getDevelopmentScheduledBuild());
                    $this->assertEquals('test_val_1', $dataObj->getStagingScheduledBuild());
                    $this->assertEquals('test_val_3', $dataObj->getProductionScheduledBuild());
                    $this->assertEquals('availability', $dataObj->getAvailability());
                    $this->assertEquals('hosting_provider2', $dataObj->getGitUrl());
                    $this->assertEquals('hosting_provider1', $dataObj->getCreatedAt());
                    $this->assertEquals('hosting_provider2', $dataObj->getUpdatedAt());

    }

    public function test_Plugin()
    {
        $dataObj =  (new Plugin())
        ->setId('name')
        ->setIsMirrored('git_url')
        ->setName("testing123")
        ->setDescription('accessibility_type')
        ->setGitUrl('access_token')
        ->setGitHubUrl('availability')
        ->setGitVersionTypeId('content')
        ->setAvailability('true1')
        ->setAccessibilityType('true')
        ->setAccessToken('git_version_type_id');

                    $this->assertEquals('name', $dataObj->getId());
                    $this->assertEquals('git_url', $dataObj->getIsMirrored());
                    $this->assertEquals('testing123', $dataObj->getName());
                    $this->assertEquals('accessibility_type', $dataObj->getDescription());
                    $this->assertEquals('access_token', $dataObj->getGitUrl());
                    $this->assertEquals('availability', $dataObj->getGitHubUrl());
                    $this->assertEquals('content', $dataObj->getGitVersionTypeId());
                    $this->assertEquals('true1', $dataObj->getAvailability());
                    $this->assertEquals('true', $dataObj->getAccessibilityType());
                    $this->assertEquals('git_version_type_id', $dataObj->getAccessToken());

    }

    public function test_ProductPlugin()
    {
        $dataObj =  (new ProductPlugin())
        ->setId('name')
        ->setProductId('git_url')
        ->setPluginId("testing123")
        ->setSelectedVersion('accessibility_type')
        ->setEnvironment('access_token');

        $dataObj->toArray();

                    $this->assertEquals('name', $dataObj->getId());
                    $this->assertEquals('git_url', $dataObj->getProductId());
                    $this->assertEquals('testing123', $dataObj->getPluginId());
                    $this->assertEquals('accessibility_type', $dataObj->getSelectedVersion());
                    $this->assertEquals('access_token', $dataObj->getEnvironment());


    }

    public function test_ProductSoftware()
    {
        $dataObj =  (new ProductSoftware())
        ->setId('name')
        ->setProductId('git_url')
        ->setSoftwareId("testing123")
        ->setSupportedVersion('accessibility_type')
        ->setSupportedVersionType('accessibility_type')
        ->setEnvironment('access_token');

        $dataObj->toArray();

                    $this->assertEquals('name', $dataObj->getId());
                    $this->assertEquals('git_url', $dataObj->getProductId());
                    $this->assertEquals('testing123', $dataObj->getSoftwareId());
                    $this->assertEquals('accessibility_type', $dataObj->getSupportedVersion());
                    $this->assertEquals('accessibility_type', $dataObj->getSupportedVersionType());
                    $this->assertEquals('access_token', $dataObj->getEnvironment());


    }

}
