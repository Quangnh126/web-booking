<?php


namespace App\Services\Web;


use App\Enums\Constant;
use App\Models\Order;
use App\Models\RequestCancelBooking;

class OrderService
{
    private $order;
    private $request_cancel;

    public function __construct(
        Order $order,
        RequestCancelBooking $requestCancelBooking
    )
    {
        $this->order = $order;
        $this->request_cancel = $requestCancelBooking;
    }

    public function makeBooking(array $data)
    {
        return $this->order->create($data);
    }

    public function updateStatus($id, $status)
    {
        $order = $this->order->ofId($id)->first();
    }

    public function getAllOrder($room_id)
    {
        $orders = $this->order->ofRoomId($room_id)
            ->whereIn('status', [Order::$pending, Order::$access])
            ->get();

        return $orders;
    }

    public function cancelOrder($id, $user_id)
    {
        $order = $this->order->ofId($id)->first();
        if ($order->status == Order::$pending) {
            $order->update(['status' => Order::$cancel]);
        } elseif ($order->status == Order::$cancel || $order->status == Order::$request_cancel || $order->status == Order::$ending) {
            return false;
        }else {
            $order->update(['status' => Order::$request_cancel]);
            $request_cancel = $this->request_cancel
                ->create(['order_id' => $id,
                    'user_id' => $user_id,
                    'status' => RequestCancelBooking::$pending]);
        }

        return $order;
    }

    public function listOrder($request, $id)
    {
        $search = $request->search;
        $type = $request->type;
        $status = $request->status;
        $perPage = $request->perpage ?? Constant::ORDER_BY;

        $orders = $this->order->with('user')
            ->join('rooms as r', 'r.id', '=', 'order.room_id')
            ->when($status, function ($query) use ($status) {
                $query->whereIn('order.status', $status);
            })
            ->when($type, function ($query) use ($type) {
                $query->whereIn('r.type_room', $type);
            })
            ->when($search, function ($query) use ($search) {
                $query->where('r.name', 'like', '%' .$search .'%');
            })
            ->where('order.user_id', $id)
            ->select('order.*', 'r.name', 'r.type', 'r.description', 'r.logo', 'r.type_room')
            ->paginate($perPage);

        return $orders;
    }

    public function detailOrder($id)
    {
        $order = $this->order->with('user')
            ->join('rooms as r', 'r.id', '=', 'order.room_id')
            ->where('order.id', $id)
            ->select('order.*', 'r.name', 'r.type', 'r.description', 'r.logo', 'r.type_room')
            ->first();

        return $order;
    }

    public function checkTimeDateBooking($startDateBooking, $endDateBooking, $startDateOrder, $endDateOrder)
    {
        $checkStartDateBooking = strtotime($endDateBooking) - strtotime($startDateOrder);
        $checkEndDateBooking = strtotime($startDateBooking) - strtotime($endDateOrder);
        if ($checkStartDateBooking >= 0 && $checkEndDateBooking >= 0) {
            return true;
        }
        return false;
    }

}
