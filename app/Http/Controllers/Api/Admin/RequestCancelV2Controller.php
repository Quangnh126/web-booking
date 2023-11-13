<?php


namespace App\Http\Controllers\Api\Admin;


use App\Enums\Constant;
use App\Http\Controllers\Controller;
use App\Http\Traits\AuthTrait;
use App\Models\Order;
use App\Models\RequestCancelBooking;
use App\Services\Admin\OrderV2Service;
use App\Services\Admin\RequestCancelV2Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RequestCancelV2Controller extends Controller
{
    use AuthTrait;

    private $requestCancel;
    private $requestCancelV2Service;
    private $order;
    private $orderV2Service;

    public function __construct(
        RequestCancelBooking $requestCancelBooking,
        RequestCancelV2Service $requestCancelV2Service,
        Order $order,
        OrderV2Service $orderV2Service
    )
    {
        $this->requestCancel = $requestCancelBooking;
        $this->requestCancelV2Service = $requestCancelV2Service;
        $this->order = $order;
        $this->orderV2Service = $orderV2Service;
    }

    /**
     * @author Quangnh
     * @OA\Get (
     *     path="/api/v2/request-cancel/index",
     *     tags={"CMS Request Cancel"},
     *     summary="CMS danh sách yêu cầu hủy booking",
     *     security={{"bearerAuth":{}}},
     *     operationId="v2/request-cancel/index",
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
     *          name="status[]",
     *          required=false,
     *          description="pending, access, cancel",
     *          @OA\Schema(
     *            type="array",
     *            @OA\Items(type="string"),
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
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *             @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Success."),
     *          )
     *     ),
     * )
     */
    public function listRequestCancel(Request $request): JsonResponse
    {
        try {
            $requestCancel = $this->requestCancelV2Service->listRequestCancel($request);

            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.success'),
                'data' => $requestCancel
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
     *     path="/api/v2/request-cancel/show/{id}",
     *     tags={"CMS Request Cancel"},
     *     summary="CMS chi tiết yêu cầu hủy booking",
     *     security={{"bearerAuth":{}}},
     *     operationId="v2/request-cancel/show",
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
     *          description="ID yêu cầu hủy booking",
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
    public function detailRequestCancel($id): JsonResponse
    {
        try {
            $requestCancel = $this->requestCancelV2Service->detailRequestCancel($id);

            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.success'),
                'data' => $requestCancel
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
     *     path="/api/v2/request-cancel/update-status/{id}",
     *     tags={"CMS Request Cancel"},
     *     summary="CMS cập nhật trạng thái yêu cầu hủy booking",
     *     security={{"bearerAuth":{}}},
     *     operationId="v2/request-cancel/update-status",
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
     *          description="ID Request Cancel",
     *          @OA\Schema(
     *            type="integer",
     *            example=1,
     *          )
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          name="status",
     *          required=true,
     *          description="Status Request Cancel: access, cancel",
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
    public function updateStatusRequestCancel(Request $request, $id): JsonResponse
    {
        try {
            $requestCancel = $this->requestCancelV2Service->updateStatusRequestCancel($id, $request);

            if (!$requestCancel) {
                return response()->json([
                    'status' => Constant::BAD_REQUEST_CODE,
                    'message' => trans('messages.errors.request_cancel.be'),
                    'data' => []
                ], Constant::BAD_REQUEST_CODE);
            }

            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.success'),
                'data' => []
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
