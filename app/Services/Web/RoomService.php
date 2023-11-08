<?php


namespace App\Services\Web;


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

}
