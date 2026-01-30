<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;


class Customer extends AppModel
{
    use HasFactory;
    protected $guarded = [];
    public function products()
    {
        return $this->belongsToMany(Product::class, 'customer_products', 'customer_id', 'product_id');
    }

    public function customerProducts()
    {
        return $this->hasMany(CustomerProduct::class, 'customer_id', 'id');
    }

    public function reseller()
    {
        return $this->belongsTo(Customer::class, 'reseller_id');
    }

    public function customerUser() {
        return $this->hasOne(User::class, 'customer_id');
    }
}
