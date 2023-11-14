<?php

namespace App\Models;

use App\Enums\Constant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use HasFactory;

    protected $table = 'comment';

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
        'user_id',
        'room_id',
        'rate',
        'content',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id')
            ->select('id', 'email', 'avatar', 'display_name', 'phone_number');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'id')
            ->select('id', 'name', 'description', 'type', 'logo', 'cost', 'start_date', 'end_date', 'status', 'type_room');
    }

    public function image()
    {
        return $this->hasMany(File::class, 'file_id', 'id')
            ->where('key', 'like', '%' . Constant::PATH_REVIEW . '%')
            ->select('id', 'key', 'file_id', 'image_data');
    }

    public function scopeOfId($query, $type)
    {
        return $query->where('id', $type);
    }
}
