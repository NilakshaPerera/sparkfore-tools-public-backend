<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductHasPlugin extends AppModel
{
    use HasFactory;
    protected $fillable = [
        "selected_version_type",
        "selected_version",
        'product_id',
        'plugin_id',
        'environment',
        'created_at',
        'updated_at'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function plugin()
    {
        return $this->belongsTo(Plugin::class, 'plugin_id');
    }
}
