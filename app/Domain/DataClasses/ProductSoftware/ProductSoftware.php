<?php

namespace App\Domain\DataClasses\ProductSoftware;

use App\Domain\DataClasses\AppDataClass;

class ProductSoftware extends AppDataClass
{
    protected $id;
    protected $productId;
    protected $softwareId;
    protected $supportedVersion;
    protected $supportedVersionType;
    protected $environment;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return ProductSoftware
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @param mixed $productId
     * @return ProductSoftware
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSoftwareId()
    {
        return $this->softwareId;
    }

    /**
     * @param mixed $software_id
     * @return ProductSoftware
     */
    public function setSoftwareId($softwareId)
    {
        $this->softwareId = $softwareId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSupportedVersion()
    {
        return $this->supportedVersion;
    }

    /**
     * @param mixed $supported_version
     * @return ProductSoftware
     */
    public function setSupportedVersion($supportedVersion)
    {
        $this->supportedVersion = $supportedVersion;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSupportedVersionType()
    {
        return $this->supportedVersionType;
    }

    /**
     * @param mixed $supported_version_type
     * @return ProductSoftware
     */
    public function setSupportedVersionType($supportedVersionType)
    {
        $this->supportedVersionType = $supportedVersionType;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @param mixed $environment
     * @return ProductSoftware
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
        return $this;
    }

    public function toArray(): array
    {
        return [
            // 'id' => $this->id,
            'product_id' => $this->productId,
            'software_id' => $this->softwareId,
            'supported_version' => $this->supportedVersion,
            'supported_version_type' => $this->supportedVersionType,
            'environment' => $this->environment
        ];
    }

}
