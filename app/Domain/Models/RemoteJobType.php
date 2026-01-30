<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class RemoteJobType extends AppModel
{
    use HasFactory;
    protected $table = 'remote_job_types';

    protected $guarded = ["id"];
}
