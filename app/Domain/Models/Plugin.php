<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Plugin extends AppModel
{
    use HasFactory;
    protected $guarded = ["id"];
    /**
     * @return BelongsToMany
     */
    public function softwares()
    {
        return $this->belongsToMany(Software::class, 'plugin_supports_softwares', 'plugin_id', 'software_id');
    }

    /**
     * @return BelongsToMany
     */
    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'plugin_available_customers');
    }

    public function pluginVersions()
    {
        return $this->hasMany(PluginVersion::class);
    }

    public function productHasPlugins()
    {
        return $this->hasMany(ProductHasPlugin::class);
    }

    public function pluginSupportsSoftwares()
    {
        return $this->hasMany(PluginSupportsSoftware::class);
    }
}
