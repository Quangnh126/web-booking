<?php


namespace App\Services\Admin;


use App\Enums\Constant;
use App\Models\Order;
use App\Models\RequestCancelBooking;
use App\Models\Room;
use App\Models\User;

class RequestCancelV2Service
{
    private $requestCancel;
    private $order;
    private $room;
    private $user;

    public function __construct(
        RequestCancelBooking $requestCancelBooking,
        Order $order,
        Room $room,
        User $user
    )
    {
        $this->requestCancel = $requestCancelBooking;
        $this->order = $order;
        $this->room = $room;
        $this->user = $user;
    }

    public function listRequestCancel($request)
    {
        $search = $request->search;
        $status = $request->status;
        $type = $request->type;
        $perPage = $request->perpage ?? Constant::ORDER_BY;

        $requestCancel = $this->requestCancel
            ->join('users as u', 'u.id', '=', 'request_cancel_booking.user_id')
            ->join('order as o', 'o.id', '=', 'request_cancel_booking.order_id')
            ->join('rooms as r', 'r.id', '=', 'o.room_id')
            ->when($search, function ($query) use ($search) {
                $query->where('r.name', 'like', '%' . $search .'%');
            })
            ->when($status, function ($query) use ($status) {
                $query->whereIn('request_cancel_booking.status', $status);
            })
            ->when($type, function ($query) use ($type) {
                $query->whereIn('r.type_room', $type);
            })
            ->orderBy('request_cancel_booking.created_at', 'desc')
            ->select('request_cancel_booking.*', 'u.email', 'u.avatar', 'u.display_name', 'u.phone_number', 'o.room_id',
                'o.cost', 'o.start_date', 'o.end_date', 'o.status as status_order', 'r.name', 'r.logo',
                'r.status as status_room', 'r.type_room')
            ->paginate($perPage);

        return $requestCancel;
    }

    public function detailRequestCancel($id)
    {
        $requestCancel = $this->requestCancel
            ->join('users as u', 'u.id', '=', 'request_cancel_booking.user_id')
            ->join('order as o', 'o.id', '=', 'request_cancel_booking.order_id')
            ->join('rooms as r', 'r.id', '=', 'o.room_id')
            ->where('request_cancel_booking.id', $id)
            ->select('request_cancel_booking.*', 'u.email', 'u.avatar', 'u.display_name', 'u.phone_number', 'o.room_id',
                'o.cost', 'o.start_date', 'o.end_date', 'o.status as status_order', 'r.name', 'r.logo',
                'r.status as status_room', 'r.type_room')
            ->first();

        return $requestCancel;
    }

    public function updateStatusRequestCancel($id, $request)
    {
        $requestCancel = $this->requestCancel->ofId($id)->first();

        if ($requestCancel->status != RequestCancelBooking::$pending) {
            return false;
        }
        $requestCancel->update(['status' => $request->status]);
        if ($request->status == RequestCancelBooking::$access) {
            $orderUpdate = $this->order->ofId($requestCancel->order_id)->update(['status' => Order::$cancel]);
        } elseif ($request->status == RequestCancelBooking::$cancel) {
            $orderUpdate = $this->order->ofId($requestCancel->order_id)->update(['status' => Order::$access]);
        }

        return $requestCancel;
    }

}
