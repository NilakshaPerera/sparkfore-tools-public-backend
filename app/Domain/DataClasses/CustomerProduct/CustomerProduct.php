<?php

namespace App\Domain\DataClasses\CustomerProduct;

use App\Domain\DataClasses\AppDataClass;

class CustomerProduct extends AppDataClass
{
    protected $id;
    protected $label;
    protected $customerId;
    protected $productId;
    protected $basePriceIncreaseYearly;
    protected $basePricingMethod;
    protected $basePricePerUserIncreaseYearly;
    protected $perUserPricingMethod;
    protected $includeMaintenance;

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

        /**
     * @param mixed $label
     * @return CustomerProduct
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @param mixed $label
     * @return CustomerProduct
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param mixed $customerId
     * @return CustomerProduct
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
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
     * @return CustomerProduct
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBasePriceIncreaseYearly()
    {
        return $this->basePriceIncreaseYearly;
    }

    /**
     * @param mixed $basePriceIncreaseYearly
     * @return CustomerProduct
     */
    public function setBasePriceIncreaseYearly($basePriceIncreaseYearly)
    {
        $this->basePriceIncreaseYearly = $basePriceIncreaseYearly;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBasePricingMethod()
    {
        return $this->basePricingMethod;
    }

    /**
     * @param mixed $basePricingMethod
     * @return CustomerProduct
     */
    public function setBasePricingMethod($basePricingMethod)
    {
        $this->basePricingMethod = $basePricingMethod;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBasePricePerUserIncreaseYearly()
    {
        return $this->basePricePerUserIncreaseYearly;
    }

    /**
     * @param mixed $basePricePerUserIncreaseYearly
     * @return CustomerProduct
     */
    public function setBasePricePerUserIncreaseYearly($basePricePerUserIncreaseYearly)
    {
        $this->basePricePerUserIncreaseYearly = $basePricePerUserIncreaseYearly;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPerUserPricingMethod()
    {
        return $this->perUserPricingMethod;
    }

    /**
     * @param mixed $perUserPricingMethod
     * @return CustomerProduct
     */
    public function setPerUserPricingMethod($perUserPricingMethod)
    {
        $this->perUserPricingMethod = $perUserPricingMethod;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIncludeMaintenance()
    {
        return $this->includeMaintenance;
    }

    /**
     * @param mixed $includeMaintenance
     * @return CustomerProduct
     */
    public function setIncludeMaintenance($includeMaintenance)
    {
        $this->includeMaintenance = $includeMaintenance;
        return $this;
    }
}
