<?php


namespace App\Http\Controllers\Api\Web;

use App\Enums\Constant;
use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Services\Web\RoomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    private $room;
    private $roomService;

    public function __construct(
        Room $room,
        RoomService $roomService
    )
    {
        $this->room = $room;
        $this->roomService = $roomService;
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
            $room = $this->roomService->filterRoom($request);

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
                ->with('banner')
                ->select('id', 'name', 'logo', 'cost', 'start_date', 'end_date', 'type_room')
                ->first();

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
