<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends AppModel
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function roleHasPermission()
    {
        $this->hasMany(RoleHasPermission::class, 'role_id');
    }

public function permissions()
{
    return $this->hasManyThrough(
        Permission::class,
        RoleHasPermission::class,
        'role_id', // Foreign key on RoleHasPermission table...
        'id', // Foreign key on Permission table...
        'id', // Local key on Role table...
        'permission_id' // Local key on RoleHasPermission table...
    );
}

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
