<?php

namespace app\Domain\Repositories\Customer;

interface CustomerRepositoryInterface
{
    public function listCustomerProducts($params);
    public function getCustomerByName($name);
    public function  storeCustomer($data);
}
