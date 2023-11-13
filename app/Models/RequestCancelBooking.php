<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestCancelBooking extends Model
{
    use HasFactory;

    protected $table = 'request_cancel_booking';

    public static $pending = 'pending';
    public static $access = 'access';
    public static $cancel = 'cancel';

    protected $hidden = [
        'updated_at',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'order_id',
        'user_id',
        'status',
        'created_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')
            ->select('id', 'email', 'avatar', 'display_name', 'address', 'phone_number');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id')
            ->select('id', 'cost', 'start_date', 'end_date', 'status');
    }

    public function scopeOfId($query, $type) {
        return $query->where('id', $type);
    }

    public function scopeOfStatus($query, $type) {
        return $query->where('status', $type);
    }

}
