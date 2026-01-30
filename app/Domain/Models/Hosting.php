<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
class Hosting extends AppModel
{
    use HasFactory, SoftDeletes;

    public function hostingProvider()
    {
        return $this->belongsTo(HostingProvider::class, 'hosting_provider_id');
    }

    public function hostingType()
    {
        return $this->belongsTo(HostingType::class, 'hosting_type_id');
    }

    public function hostingCustomers()
    {
        return $this->belongsToMany(Customer::class, 'hosting_available_customers', 'hosting_id', 'customer_id');
    }

    public function hostingCloudSettings()
    {
        return $this->hasOne(HostingCloudSetting::class);
    }

    public function basePackage()
    {
        return $this->belongsTo(BasePackage::class);
    }
}
