<?php

namespace App\Domain\DataClasses\Installation;

use App\Domain\DataClasses\AppDataClass;

class DeleteInstallationDto extends AppDataClass
{
    protected $cloud;
    protected $location;
    protected $subdomain;
    protected $domain;
    protected $jobId;
    protected $callbackUrl;

    public function setFromInstallation($installationModel) {
        $installationModel->load('hosting.hostingProvider');
        $installationModel->load('customerProduct.customer');
        $installationModel->load('customerProduct.product.productSoftwares.software');
        $this->cloud = $installationModel->hosting->hostingProvider->key;
        $this->location = $installationModel->hosting->hosting_location;

        $urlParts = explode('.', $installationModel->url);
        // Get the subdomain (the first part)
        $this->subdomain = implode('.', array_slice($urlParts, 0, count($urlParts) - 2)); // subdomain.example
        // Get the domain (the last two parts)
        $this->domain = implode('.', array_slice($urlParts, -2));  // example.com
    }

    public function setCloud($cloud)
    {
        $this->cloud = $cloud;
    }

    public function setLocation($location)
    {
        $this->location = $location;
    }

    public function setSubdomain($subdomain)
    {
        $this->subdomain = $subdomain;
    }

    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    public function setCallbackUrl($callbackUrl)
    {
        $this->callbackUrl = $callbackUrl;
    }

    public function setJobId($jobId)
    {
        $this->jobId = $jobId;
    }
}
