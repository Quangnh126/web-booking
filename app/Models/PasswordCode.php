<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordCode extends Model
{
    use HasFactory;

    protected $table = 'password_codes';

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'code',
    ];

    public function scopeOfEmail($query, $type)
    {
        return $query->when($type, function ($query, $type){
            $query->where('email', $type);
        });
    }

    public function scopeOfCode($query, $type)
    {
        return $query->when($type, function ($query, $type){
            $query->where('code', $type);
        });
    }
}
