<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductHasSoftware extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'software_id',
        'supported_version',
        'supported_version_type',
        'environment',
        'created_at',
        'updated_at',
    ];

    public $timestamps = true;

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function software()
    {
        return $this->belongsTo(Software::class, 'software_id');
    }
}
