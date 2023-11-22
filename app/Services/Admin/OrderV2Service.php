<?php


namespace App\Services\Admin;


use App\Enums\Constant;
use App\Models\Order;

class OrderV2Service
{
    private $order;

    public function __construct(
        Order $order
    )
    {
        $this->order = $order;
    }

    public function listOrder($request)
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
            ->orderBy('order.created_at', 'desc')
            ->select('order.*', 'r.name', 'r.type', 'r.description', 'r.logo', 'r.type_room')
            ->paginate($perPage);

        return $orders;
    }

    public function detailOrder($id)
    {
        $order = $this->order->with('user')
            ->join('rooms as r', 'r.id', '=', 'order.room_id')
            ->where('order.id', '=', $id)
            ->select('order.*', 'r.name', 'r.type', 'r.description', 'r.logo', 'r.type_room')
            ->first();

        return $order;
    }

    public function updateStatusOrder($id, $request)
    {
        $status = $request->status;

        $order = $this->order->ofId($id)->first();
        $order->update(['status' => $status]);

        return $order;
    }

}
