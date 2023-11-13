<?php


namespace App\Http\Controllers\Api\Admin;


use App\Enums\Constant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderV2Request;
use App\Models\Order;
use App\Services\Admin\OrderV2Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderV2Controller extends Controller
{
    private $order;
    private $orderV2Service;

    public function __construct(
        Order $order,
        OrderV2Service $orderV2Service
    )
    {
        $this->order = $order;
        $this->orderV2Service = $orderV2Service;
    }

    /**
     * @author Quangnh
     * @OA\Get (
     *     path="/api/order/list-order",
     *     tags={"Order"},
     *     summary="Danh sách đơn booking",
     *     security={{"bearerAuth":{}}},
     *     operationId="order/index",
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
     *          name="search",
     *          required=false,
     *          description="Tìm kiếm theo tên Room, Tour",
     *          @OA\Schema(
     *            type="string",
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
     *          name="status[]",
     *          required=false,
     *          description="pending, access, endding, cancel",
     *          @OA\Schema(
     *            type="array",
     *            @OA\Items(type="string"),
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
    public function listOrder(Request $request): JsonResponse
    {
        try {

            $orders = $this->orderV2Service->listOrder($request);

            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.success'),
                'data' => $orders
            ], Constant::SUCCESS_CODE);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => Constant::FALSE_CODE,
                'message' => $th->getMessage(),
                'data' => []
            ], Constant::INTERNAL_SV_ERROR_CODE);
        }
    }

    /**
     * @author Quangnh
     * @OA\Get (
     *     path="/api/v2/order/show/{id}",
     *     tags={"CMS Order"},
     *     summary="CMS chi tiết order",
     *     security={{"bearerAuth":{}}},
     *     operationId="v2/order/show",
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
     *          required=true,
     *          description="ID Order",
     *          @OA\Schema(
     *            type="integer",
     *            example=1,
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
    public function detailOrder($id): JsonResponse
    {
        try {

            $orders = $this->orderV2Service->detailOrder($id);

            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.success'),
                'data' => $orders
            ], Constant::SUCCESS_CODE);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => Constant::FALSE_CODE,
                'message' => $th->getMessage(),
                'data' => []
            ], Constant::INTERNAL_SV_ERROR_CODE);
        }
    }

    /**
     * @author Quangnh
     * @OA\POST (
     *     path="/api/v2/order/update-status/{id}",
     *     tags={"CMS Order"},
     *     summary="CMS cập nhật trạng thái order",
     *     security={{"bearerAuth":{}}},
     *     operationId="v2/order/update-status",
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
     *          required=true,
     *          description="ID Order",
     *          @OA\Schema(
     *            type="integer",
     *            example=1,
     *          )
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          name="status",
     *          required=true,
     *          description="Status Order: access, ending, cancel",
     *          @OA\Schema(
     *            type="string",
     *            example="access",
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
    public function updateStatusOrder(OrderV2Request $request, $id): JsonResponse
    {
        try {

            $orders = $this->orderV2Service->updateStatusOrder($id, $request);

            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.success'),
                'data' => $orders
            ], Constant::SUCCESS_CODE);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => Constant::FALSE_CODE,
                'message' => $th->getMessage(),
                'data' => []
            ], Constant::INTERNAL_SV_ERROR_CODE);
        }
    }

}
