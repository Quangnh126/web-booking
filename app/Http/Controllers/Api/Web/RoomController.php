<?php


namespace App\Http\Controllers\Api\Web;


use App\Http\Controllers\Controller;
use App\Services\Web\RoomService;

class RoomController extends Controller
{
    private $roomService;

    public function __construct(
        RoomService $roomService
    )
    {
        $this->roomService = $roomService;
    }

}
