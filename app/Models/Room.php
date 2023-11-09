<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Room extends Model
{
    use HasFactory;

    protected $table = 'rooms';
    public static $active = 1;
    public static $non_active = 0;

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
        'name',
        'type',
        'description',
        'logo',
        'cost',
        'start_date',
        'end_date',
        'status',
        'type_room',
    ];

    public function banner(): HasMany
    {
        return $this->hasMany(File::class, 'file_id', 'id')
            ->where('key', File::$room)
            ->select('id', 'file_id', 'image_data');
    }

    public function categories(): HasOne
    {
        return $this->hasOne(Category::class, 'id', 'type')
            ->select('id', 'name', 'description');
    }

    public function scopeOfActive()
    {
        return $this->where('status', '=', 1);
    }

}
