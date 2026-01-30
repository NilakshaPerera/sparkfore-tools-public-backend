<?php

namespace app\Domain\DataClasses\Customer;

use App\Domain\DataClasses\AppDataClass;

class Customer extends AppDataClass
{
    protected $id;
    protected $name;
    protected $slugifiedName;
    protected $organizationNo;
    protected $invoiceType;
    protected $invoiceAddress;
    protected $invoiceEmail;
    protected $invoiceReference;
    protected $invoiceAnnotation;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Customer
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function setSlugifiedName($slugifiedName)
    {
        $this->slugifiedName = $slugifiedName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSlugifiedName()
    {
        return $this->slugifiedName;
    }

    /**
     * @param mixed $name
     * @return Customer
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrganizationNo()
    {
        return $this->organizationNo;
    }

    /**
     * @param mixed $organizationNo
     * @return Customer
     */
    public function setOrganizationNo($organizationNo)
    {
        $this->organizationNo = $organizationNo;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInvoiceType()
    {
        return $this->invoiceType;
    }

    /**
     * @param mixed $invoiceType
     * @return Customer
     */
    public function setInvoiceType($invoiceType)
    {
        $this->invoiceType = $invoiceType;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInvoiceAddress()
    {
        return $this->invoiceAddress;
    }

    /**
     * @param mixed $invoiceAddress
     * @return Customer
     */
    public function setInvoiceAddress($invoiceAddress)
    {
        $this->invoiceAddress = $invoiceAddress;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInvoiceEmail()
    {
        return $this->invoiceEmail;
    }

    /**
     * @param mixed $invoiceEmail
     * @return Customer
     */
    public function setInvoiceEmail($invoiceEmail)
    {
        $this->invoiceEmail = $invoiceEmail;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInvoiceReference()
    {
        return $this->invoiceReference;
    }

    /**
     * @param mixed $invoiceReference
     * @return Customer
     */
    public function setInvoiceReference($invoiceReference)
    {
        $this->invoiceReference = $invoiceReference;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInvoiceAnnotation()
    {
        return $this->invoiceAnnotation;
    }

    /**
     * @param mixed $invoiceAnnotation
     * @return Customer
     */
    public function setInvoiceAnnotation($invoiceAnnotation)
    {
        $this->invoiceAnnotation = $invoiceAnnotation;
        return $this;
    }
}
