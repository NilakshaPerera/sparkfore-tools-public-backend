<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductHasPluginVersions extends AppModel
{
    use HasFactory;

    protected $fillable = [
        'product_has_plugin_id',
        'include',
        'selected_version',
        'environment'
    ];

    public function productPlugin()
    {
        return $this->belongsTo(ProductHasPlugin::class, 'product_has_plugin_id');
    }
}
