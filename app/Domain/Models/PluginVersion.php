<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PluginVersion extends AppModel
{
    use HasFactory;
    public function plugin()
    {
        return $this->belongsTo(PluginVersion::class, 'plugin_id');
    }
    public function versionType()
    {
        return $this->belongsTo(GitVersionType::class, 'version_type');
    }
}
