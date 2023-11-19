<?php


namespace App\Http\Controllers\Api\Web;

use App\Enums\Constant;
use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Services\Web\OrderService;
use App\Services\Web\RoomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    private $room;
    private $roomService;
    private $orderService;

    public function __construct(
        Room $room,
        RoomService $roomService,
        OrderService $orderService
    )
    {
        $this->room = $room;
        $this->roomService = $roomService;
        $this->orderService = $orderService;
    }

    /**
     * @OA\GET (
     *     path="/api/room",
     *     tags={"Room"},
     *     summary="Danh sách Room, Tour",
     *     security={{"bearerAuth":{}}},
     *     operationId="/room",
     *     @OA\Parameter(
     *          in="header",
     *          name="language",
     *          required=false,
     *          description="Ngôn ngữ",
     *          @OA\Schema(
     *            type="string",
     *            example="vi",
     *          )
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          name="page",
     *          required=false,
     *          description="Trang",
     *          @OA\Schema(
     *            type="integer",
     *            example=1,
     *          )
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          name="perpage",
     *          required=false,
     *          description="Per Page",
     *          @OA\Schema(
     *            type="integer",
     *            example=10,
     *          )
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          name="type[]",
     *          required=false,
     *          description="room, tour",
     *          @OA\Schema(
     *            type="array",
     *            @OA\Items(type="string"),
     *          )
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          name="category[]",
     *          required=false,
     *          description="type category",
     *          @OA\Schema(
     *            type="array",
     *            @OA\Items(type="integer"),
     *          )
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          name="search",
     *          required=false,
     *          description="Tìm kiếm theo tên",
     *          @OA\Schema(
     *            type="string",
     *            example="tour",
     *          )
     *      ),
     *      @OA\Parameter(
     *          in="query",
     *          name="cost_min",
     *          required=false,
     *          description="Tìm kiếm theo giá min",
     *          @OA\Schema(
     *            type="integer",
     *          )
     *      ),
     *      @OA\Parameter(
     *          in="query",
     *          name="cost_max",
     *          required=false,
     *          description="Tìm kiếm theo giá max",
     *          @OA\Schema(
     *            type="integer",
     *          )
     *      ),
     *     @OA\Parameter(
     *          in="query",
     *          name="sort_cost",
     *          required=false,
     *          description="Sắp xếp theo giá: asc: tăng dần, desc: giảm dần",
     *          @OA\Schema(
     *            type="string",
     *          )
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *             @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Success."),
     *          )
     *     ),
     * )
     */
    public function index(Request $request):JsonResponse
    {
        try {
            $rooms = $this->roomService->filterRoom($request);

            foreach ($rooms as $room) {
                if ($room->type_room == 'tour') {
                    $allOrder = $this->orderService->getAllOrder($room->id);
                    $room->can_order = (int) $room->categories->number - count($allOrder);
                } else {
                    $room->can_order = 0;
                }
            }

            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.success'),
                'data' => $rooms,
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => Constant::INTERNAL_SV_ERROR_CODE,
                'message' => $th,
                'data' => []
            ], Constant::INTERNAL_SV_ERROR_CODE);
        }
    }

    /**
     * @OA\GET (
     *     path="/api/room/detail/{id}",
     *     tags={"Room"},
     *     summary="Chi tiết Room, Tour",
     *     security={{"bearerAuth":{}}},
     *     operationId="/room/detail",
     *     @OA\Parameter(
     *          in="header",
     *          name="language",
     *          required=false,
     *          description="Ngôn ngữ",
     *          @OA\Schema(
     *            type="string",
     *            example="vi",
     *          )
     *     ),
     *     @OA\Parameter(
     *          in="path",
     *          name="id",
     *          required=false,
     *          description="Ngôn ngữ",
     *          @OA\Schema(
     *            type="string",
     *            example="6",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *             @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Success."),
     *          )
     *     ),
     * )
     */
    public function show($id): JsonResponse
    {
        try {
            $room = $this->room->ofActive()
                ->where('id', $id)
                ->with('banner', 'categories')
                ->select('id', 'name', 'description', 'type', 'logo', 'cost', 'start_date', 'end_date', 'type_room')
                ->first();

            if ($room->type_room == 'tour') {
                $orderRoom = $this->orderService->getAllOrder($id);
                $room->can_order = (int) $room->categories->number - count($orderRoom);
            } else {
                $room->can_order = 0;
            }

            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.success'),
                'data' => $room,
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => Constant::INTERNAL_SV_ERROR_CODE,
                'message' => $th,
                'data' => []
            ], Constant::INTERNAL_SV_ERROR_CODE);
        }
    }

}
