<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class PublicInstallation extends Model
{
    protected $fillable = [
        'site_name',
        'password',
        'first_name',
        'last_name',
        'email',
        'phone',
        'terms_accepted',
        'remote_job_id'
    ];

    public function user(){
        return $this->belongsTo(User::class, "email", "email");
    }
}
