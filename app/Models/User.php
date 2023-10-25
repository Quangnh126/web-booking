<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $connection = 'mysql';

    protected $table = 'users';

    static $active = 1;
    static $inactive = 0;
    static $admin = 1;
    static $user = 2;
    static $staff = 3;
    static $verify = 1;
    static $not_verify = 0;
    static $register_code = 0;
    static $forgot_code = 1;
    static $has_edit = 1;
    static $not_edit = 0;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'avatar',
        'display_name',
        'phone_number',
        'status',
        'role_id',
        'has_edit',
        'verify',
        'detail_address',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'device_token',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function scopeOfRole($query, $type)
    {
        return $query->where('role_id', $type);
    }

    public function scopeOfEmail($query, $type)
    {
        return $query->where('email', $type);
    }

    public function scopeOfStaff($query, $type)
    {
        return $query->whereIn('role_id', $type);
    }
}
