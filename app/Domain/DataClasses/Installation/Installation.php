<?php

namespace App\Domain\DataClasses\Installation;

use App\Domain\DataClasses\AppDataClass;

class Installation extends AppDataClass
{
    protected $customerProductId;
    protected $id;
    protected $hostingId;
    protected $domainType;
    protected $domain;
    protected $subDomain;
    protected $hostingTypeId;
    protected $hostingProviderId;
    protected $url;
    protected $includeStagingPackage;
    protected $includeBackup;
    protected $generalTermsAgreement;
    protected $billingTermsAgreement;
    protected $installationTargetTypeId;
    protected $status;

    /**
     * @return mixed
     */
    public function getCustomerProductId()
    {
        return $this->customerProductId;
    }

    /**
     * @param mixed $customerProductId
     * @return Installation
     */
    public function setCustomerProductId($customerProductId)
    {
        $this->customerProductId = $customerProductId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $customerProductId
     * @return Installation
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

        /**
     * @return mixed
     */
    public function getInstallationTargetTypeId()
    {
        return $this->installationTargetTypeId;
    }

    /**
     * @param mixed $installationTargetTypeId
     * @return Installation
     */
    public function setInstallationTargetTypeId($installationTargetTypeId)
    {
        $this->installationTargetTypeId = $installationTargetTypeId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHostingId()
    {
        return $this->hostingId;
    }

    /**
     * @param mixed $hostingId
     * @return Installation
     */
    public function setHostingId($hostingId)
    {
        $this->hostingId = $hostingId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDomainType()
    {
        return $this->domainType;
    }

    /**
     * @param mixed $domainType
     * @return Installation
     */
    public function setDomainType($domainType)
    {
        $this->domainType = $domainType;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param mixed $domain
     * @return Installation
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSubDomain()
    {
        return $this->subDomain;
    }

    /**
     * @param mixed $sub_domain
     * @return Installation
     */
    public function setSubDomain($subDomain)
    {
        $this->subDomain = $subDomain;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     * @return Installation
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIncludeBackup()
    {
        return $this->includeBackup;
    }

    /**
     * @param mixed $includeBackup
     * @return Installation
     */
    public function setIncludeBackup($includeBackup)
    {
        $this->includeBackup = $includeBackup;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGeneralTermsAgreement()
    {
        return $this->generalTermsAgreement;
    }

    /**
     * @param mixed $generalTermsAgreement
     * @return Installation
     */
    public function setGeneralTermsAgreement($generalTermsAgreement)
    {
        $this->generalTermsAgreement = $generalTermsAgreement;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBillingTermsAgreement()
    {
        return $this->billingTermsAgreement;
    }

    /**
     * @param mixed $billingTermsAgreement
     * @return Installation
     */
    public function setBillingTermsAgreement($billingTermsAgreement)
    {
        $this->billingTermsAgreement = $billingTermsAgreement;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     * @return Installation
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHostingProviderId()
    {
        return $this->hostingProviderId;
    }

    /**
     * @param mixed $hostingProviderId
     * @return Installation
     */
    public function setHostingProviderId($hostingProviderId)
    {
        $this->hostingProviderId = $hostingProviderId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHostingTypeId()
    {
        return $this->hostingTypeId;
    }

    /**
     * @param mixed $hostingTypeId
     * @return Installation
     */
    public function setHostingTypeId($hostingTypeId)
    {
        $this->hostingTypeId = $hostingTypeId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIncludeStagingPackage()
    {
        return $this->includeStagingPackage;
    }

    /**
     * @param mixed $includeStagingPackage
     * @return Installation
     */
    public function setIncludeStagingPackage($includeStagingPackage)
    {
        $this->includeStagingPackage = $includeStagingPackage;
        return $this;
    }
}
