<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    protected $table = 'order';

    public static $pending = 'pending';
    public static $access = 'access';
    public static $ending = 'ending';
    public static $cancel = 'cancel';
    public static $request_cancel = 'pending_cancel';

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
        'id',
        'user_id',
        'room_id',
        'cost',
        'start_date',
        'end_date',
        'status',
    ];

    public function roomOrTour(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id', 'id')
            ->select('id', 'name', 'description', 'type', 'logo', 'cost', 'start_date', 'end_date', 'status', 'type_room');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id')
            ->select('id', 'email', 'avatar', 'display_name', 'phone_number');
    }

    public function scopeOfId($query, $type)
    {
        return $query->where('id', $type);
    }

    public function scopeOfRoomId($query, $type)
    {
        return $query->where('room_id', $type);
    }

    public function scopeOfHasOrder()
    {
        return $this->whereIn('status', ['pending', 'access']);
    }

}
