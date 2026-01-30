<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;


class Software extends AppModel
{
    use HasFactory, SoftDeletes;
    protected $table = 'softwares';
    protected $guarded = ["id"];
    protected $fillable = ['name',  'git_url', 'git_version_type_id', 'version_supported' , 'slug', 'software_slug'];
    public function gitVersionType()
    {
        return $this->belongsTo(GitVersionType::class);
    }

    public function softwareSlug()
    {
        return $this->belongsTo(SoftwareSlug::class, 'software_slug');
    }
}
