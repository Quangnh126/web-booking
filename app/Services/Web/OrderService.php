<?php


namespace App\Services\Web;


use App\Models\Order;

class OrderService
{
    private $order;

    public function __construct(
        Order $order
    )
    {
        $this->order = $order;
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

    public function cancelOrder($id)
    {
        $order = $this->order->ofId($id)->first();
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
