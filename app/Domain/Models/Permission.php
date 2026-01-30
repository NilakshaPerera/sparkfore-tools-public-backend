<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Permission extends AppModel
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'name',
        'codename',
        'description',
        'action', // create, read, update, delete, soft_delete
    ];
    protected $appends = ['created_time_ago'];

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    public function roleHasPermission()
    {
        return $this->hasMany(RoleHasPermission::class, 'permission_id');
    }

    public function getCreatedTimeAgoAttribute()
    {
        $created = new Carbon($this->attributes['created_at']);
        $now = Carbon::now();
        return $created->diffForHumans($now);
    }
}
