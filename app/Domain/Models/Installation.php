<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Installation extends AppModel
{
    use HasFactory;
    protected $fillable = [
        'available_disk_space',
        'status_code',
        'updated_at',
        'software_version',
        'hosting_id',
        'include_staging_package',
        'include_backup',
        'general_terms_agreement',
        'billing_terms_agreement',
        'date_contract_ends',
        'date_contract_terminate',
        'installation_target_type_id',
        'customer_product_id',
        'domain_type',
        'url',
        'status',
        'created_at',
        'disk_size',
    ];

    public function customerProduct()
    {
        return $this->belongsTo(CustomerProduct::class);
    }

    public function hosting()
    {
        return $this->belongsTo(Hosting::class);
    }

    public function targetType()
    {
        return $this->belongsTo(InstallationTargetType::class, 'installation_target_type_id');
    }
}
