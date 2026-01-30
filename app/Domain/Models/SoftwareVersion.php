<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoftwareVersion extends Model
{
    use HasFactory;

    protected $guarded = ["id"];

    protected $fillable = [
        'software_id',
        'version_name',
        'version_id',
        'version_type',
        'php_version',
        'major_version',
        'minor_version',
        'patch_version',
        'prefix',
        'branch_version',
    ];  

    public function software()
    {
        return $this->belongsTo(Software::class, 'software_id');
    }
}
