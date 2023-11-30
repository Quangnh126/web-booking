<?php


namespace App\Services\Admin;


use App\Models\Order;
use App\Models\Room;
use App\Models\User;

class DashboardV2Service
{
    private $user;
    private $order;
    private $room;

    public function __construct(
        User $user,
        Order $order,
        Room $room
    )
    {
        $this->user = $user;
        $this->order = $order;
        $this->room = $room;
    }

    public function general()
    {
        $data = [];
        $customer = $this->user->ofRole(User::$user)
            ->ofStatus(User::$active)
            ->count();

        $order_pending = $this->order->ofStatus(Order::$pending)
            ->count();

        $order_access = $this->order->ofStatus(Order::$access)
            ->count();

        $order_ending = $this->order->ofStatus(Order::$ending)
            ->count();

        $order_cancel = $this->order->ofStatus(Order::$cancel)
            ->count();

        $order_pending_cancel = $this->order->ofStatus(Order::$request_cancel)
            ->count();

        $room = $this->room->ofType(Room::$room)
            ->where('status', 1)
            ->count();

        $tour = $this->room->ofType(Room::$tour)
            ->where('status', 1)
            ->count();

        $data['customer'] = $customer;
        $data['order_pending'] = $order_pending;
        $data['order_access'] = $order_access;
        $data['order_ending'] = $order_ending;
        $data['order_cancel'] = $order_cancel;
        $data['order_pending_cancel'] = $order_pending_cancel;
        $data['room'] = $room;
        $data['tour'] = $tour;

        return $data;

    }

}
