<?php


namespace App\Services\Web;


use App\Enums\Constant;
use App\Models\Room;

class RoomService
{
    private $room;

    public function __construct(
        Room $room
    )
    {
        $this->room = $room;
    }

    public function filterRoom($rq)
    {
        $type = $rq->type ?: [];
        $search = $rq->search;
        $costMin = $rq->cost_min;
        $costMax = $rq->cost_max;
        $category = $rq->category ?: [];
        $sort_cost = $rq->sort_cost;
        $perPage = $rq->perpage ?: Constant::ORDER_BY;

        $room = $this->room->ofActive()
            ->with('categories')
            ->when($type, function ($query) use ($type) {
                $query->whereIn('type_room', $type);
            })
            ->when($category, function ($query) use ($category) {
                $query->whereIn('type', $category);
            })
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->when($costMin, function ($query) use ($costMin) {
                $query->where('cost', '>=', (int) $costMin);
            })
            ->when($costMax, function ($query) use ($costMax) {
                $query->where('cost', '<=', (int) $costMax);
            })
            ->when($sort_cost, function ($query) use ($sort_cost) {
                $query->orderBy('cost', $sort_cost);
            })
            ->select('id', 'name', 'description', 'logo', 'cost', 'start_date', 'end_date', 'type_room', 'type')
            ->paginate($perPage);

        return $room;
    }

}
