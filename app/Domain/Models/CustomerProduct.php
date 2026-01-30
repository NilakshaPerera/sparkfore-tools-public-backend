<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;


class CustomerProduct extends AppModel
{
    use HasFactory;
    protected $table = 'customer_products';

    protected $fillable = [
        'label',
        'base_price_increase_yearly',
        'base_price_per_user_increase_yearly',
        'include_maintenance',
        'customer_id',
        'product_id'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function installations()
    {
        return $this->hasMany(Installation::class, 'customer_product_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
