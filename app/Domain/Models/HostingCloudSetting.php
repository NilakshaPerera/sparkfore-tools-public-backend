<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class HostingCloudSetting extends AppModel
{
    use HasFactory;
    protected $table = 'hosting_cloud_settings';

    protected $fillable = [
        'hosting_id',
        'base_package_id',
        'hosting_provider_id',
        'backup_price_monthly',
        'staging_price_monthly',
        'active',
        'created_at',
        'updated_at',
    ];

    public function hostingProvider()
    {
        return $this->belongsTo(HostingProvider::class);
    }
}
