<?php


namespace App\Http\Controllers\Api\Web;


use App\Enums\Constant;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Room;
use App\Models\User;
use App\Services\Web\OrderService;
use App\Services\Web\RoomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    private $room;
    private $roomService;
    private $order;
    private $orderService;
    private $user;

    public function __construct(
        Room $room,
        RoomService $roomService,
        Order $order,
        OrderService $orderService,
        User $user
    )
    {
        $this->room = $room;
        $this->roomService = $roomService;
        $this->order = $order;
        $this->orderService = $orderService;
        $this->user = $user;
    }

    /**
     * @author Quangnh
     * @OA\Post (
     *     path="/api/order/booking-room",
     *     tags={"Order"},
     *     summary="Booking room",
     *     security={{"bearerAuth":{}}},
     *     operationId="/order/booking-room",
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
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 allOf={
     *                     @OA\Schema(
     *                          @OA\Property(property="id_room", type="string"),
     *                          @OA\Property(property="id_user", type="string"),
     *                          @OA\Property(property="start_date", type="string"),
     *                          @OA\Property(property="end_date", type="string"),
     *                          )
     *                      }
     *                     )
     *                  )
     *              ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *             @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Success."),
     *          )
     *     ),
     * )
     */
    public function makeBookingRoom(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $this->getFormOrder($request);

            $user = $this->user->ofId($data['user_id'])
                ->ofStatus(User::$active)
                ->first();

            if(!$user) {
                DB::rollBack();

                return response()->json([
                    'status' => Constant::BAD_REQUEST_CODE,
                    'message' => trans('messages.errors.users.not_found'),
                    'data' => []
                ], Constant::BAD_REQUEST_CODE);
            }

            $room = $this->room->with('categories')
                ->ofActive()
                ->ofId($data['room_id'])
                ->first();

            if(!$room) {
                DB::rollBack();

                return response()->json([
                    'status' => Constant::BAD_REQUEST_CODE,
                    'message' => trans('messages.errors.room.not_found'),
                    'data' => []
                ], Constant::BAD_REQUEST_CODE);
            }

            $data['cost'] = $room->cost;

            $checkOrderRoom = $this->orderService->getAllOrder($data['room_id']);
            $accept = 0;

            foreach ($checkOrderRoom as $order) {
                if (!$this->orderService->checkTimeDateBooking($data['start_date'], $data['end_date'], $order->start_date, $order->end_date)){
                    $accept++;
                }
                if ($accept > 0) {
                    DB::rollBack();

                    return response()->json([
                        'status' => Constant::BAD_REQUEST_CODE,
                        'message' => trans('messages.errors.order.room-booking'),
                        'data' => []

                    ], Constant::BAD_REQUEST_CODE);

                    break;
                }
            }

            $order = $this->orderService->makeBooking($data);

            DB::commit();

            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.order.booking'),
                'data' => $order

            ], Constant::SUCCESS_CODE);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => Constant::FALSE_CODE,
                'message' => $th->getMessage()
            ], Constant::INTERNAL_SV_ERROR_CODE);
        }
    }

    /**
     * @author Quangnh
     * @OA\Post (
     *     path="/api/order/booking-tour",
     *     tags={"Order"},
     *     summary="Booking tour",
     *     security={{"bearerAuth":{}}},
     *     operationId="/order/booking-tour",
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
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 allOf={
     *                     @OA\Schema(
     *                          @OA\Property(property="id_room", type="string"),
     *                          @OA\Property(property="id_user", type="string"),
     *                          )
     *                      }
     *                     )
     *                  )
     *              ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *             @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Success."),
     *          )
     *     ),
     * )
     */
    public function makeBookingTour(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $this->getFormOrder($request);

            $user = $this->user->ofId($data['user_id'])
                ->ofStatus(User::$active)
                ->first();

            if(!$user) {
                DB::rollBack();

                return response()->json([
                    'status' => Constant::BAD_REQUEST_CODE,
                    'message' => trans('messages.errors.users.not_found'),
                    'data' => []
                ], Constant::BAD_REQUEST_CODE);
            }

            $tour = $this->room->with('categories')
                ->ofActive()
                ->ofId($data['room_id'])
                ->first();

            if(!$tour) {
                DB::rollBack();

                return response()->json([
                    'status' => Constant::BAD_REQUEST_CODE,
                    'message' => trans('messages.errors.room.not_found'),
                    'data' => []
                ], Constant::BAD_REQUEST_CODE);
            }

            $data['cost'] = $tour->cost;

            $checkOrderTour = $this->orderService->getAllOrder($data['room_id']);

            if (count($checkOrderTour) >= (int) $tour->categories->number) {
                return response()->json([
                    'status' => Constant::BAD_REQUEST_CODE,
                    'message' => trans('messages.errors.order.tour-booking'),
                    'data' => []

                ], Constant::BAD_REQUEST_CODE);
            }

            $data['start_date'] = $tour->start_date;
            $data['end_date']  = $tour->end_date;

            $order = $this->orderService->makeBooking($data);

            DB::commit();

            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.order.booking'),
                'data' => $order

            ], Constant::SUCCESS_CODE);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => Constant::FALSE_CODE,
                'message' => $th->getMessage()
            ], Constant::INTERNAL_SV_ERROR_CODE);
        }
    }

    public function getFormOrder($request)
    {
        $data = [];

        $data['room_id'] = $request->id_room;
        $data['user_id'] = $request->id_user;

        if ($request->start_date) {
            $data['start_date'] = $request->start_date;
        }

        if ($request->end_date) {
            $data['end_date'] = $request->end_date;
        }

        $data['status'] = Order::$pending;

        return $data;
    }

}
