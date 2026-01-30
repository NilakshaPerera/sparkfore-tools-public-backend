<?php

namespace App\Domain\Repositories\Customer;

use App\Domain\Models\Customer;
use App\Domain\Models\CustomerProduct;
use Illuminate\Database\Eloquent\Model;

class CustomerRepository implements CustomerRepositoryInterface
{
    public function __construct(
        protected Customer $customer,
        protected CustomerProduct $customerProduct
    ) {}

    /**
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        return $this->customer::find($id, [
            'id',
            'name',
            'slugified_name',
            'organization_no',
            'invoice_type',
            'invoice_address',
            'invoice_email',
            'invoice_reference',
            'invoice_annotation'
        ]);
    }

    /**
     * @param $params
     * @return mixed
     */
    public function updateCustomer($params)
    {
        $id = $params['id'];
        $this->customer->where('id', $id)->update($params);
        return $id;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function storeCustomer($data)
    {
        return $this->customer::insertGetId($data);
    }

    public function listCustomers($params)
    {
        // Modal
        $customer = $this->customer;

        if (!empty($with = ($params['with'] ?? false))) {
            $customer = $customer->with($with);
        }

        if (!empty($filter = ($params['filter'] ?? false))) {
            $customer = $customer->where(function ($q) use ($filter) {
                $q->where('name', 'ilike', "%$filter%")
                    ->orWhere('invoice_email', 'ilike', "%$filter%")
                    ->orWhere('invoice_address', 'ilike', "%$filter%");
            });
        }

        $customer = $customer->orderBy('id', 'desc');

        if (!empty($page = ($params['page'] ?? false)) && !empty($perPage = ($params['per_page'] ?? false))) {
            return $customer->paginate($perPage, ['*'], 'page', $page);
        }

        return $customer->get();
    }

    public function listCustomerProducts($params)
    {
        $customerProduct = $this->customerProduct
            ->where('customer_id', $params['id']);

        if (!empty($with = ($params['with'] ?? false))) {
            $customerProduct = $customerProduct->with($with);
        }

        return $customerProduct->get();
    }

    /**
     * @return Builder[]|Collection
     */
    public function getCustomerByName($name)
    {
        return $this->customer->select(['id'])
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->first();
    }
}
