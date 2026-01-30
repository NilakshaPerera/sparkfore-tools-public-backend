<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Carbon\Carbon;
use Log;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'f_name',
        'l_name',
        'email',
        'password',
        'lang_id',
        'first_login',
        'last_login',
        'trial',
        'role_id',
        'account_type',
        'customer_id',
        'suspended_at',
        'account_type_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['last_login_ago'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function accountType()
    {
        return $this->belongsTo(AccountType::class, 'account_type_id');
    }

    public function getLastLoginAgoAttribute()
    {
        if (isset($this->attributes['last_login'])) {
            $created = new Carbon();
            $now = Carbon::now();
            return $created->diffForHumans($now);
        } else {
            return "Not logged in";
        }
    }

    public function hasPermissionTo(array $permissions)
    {
        // Check if the user has at least one of the provided permissions
        return $this->role->permissions->whereIn('codename', $permissions)->isNotEmpty();
    }
}
