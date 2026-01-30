<?php

namespace App\Domain\Models;

class ProductAvailableCustomer extends AppModel
{
    protected $table = 'product_available_customers';
    protected $fillable = ['customer_id', 'product_id'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
