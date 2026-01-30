<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Module extends AppModel
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];
    protected $appends = ['created_time_ago'];

    public function permission()
    {
        $this->hasMany(Permission::class, 'module_id');
    }

    public function getCreatedTimeAgoAttribute()
    {
        $created = new Carbon($this->attributes['created_at']);
        $now = Carbon::now();
        return $created->diffForHumans($now);
    }
}
