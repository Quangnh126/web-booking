<?php


namespace App\Http\Controllers\Api\Admin;


use App\Enums\Constant;
use App\Http\Controllers\Controller;
use App\Services\Admin\ReviewV2Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewV2Controller extends Controller
{
    private $reviewV2Service;

    public function __construct(
        ReviewV2Service $reviewV2Service
    )
    {
        $this->reviewV2Service = $reviewV2Service;
    }

    /**
     * @OA\Get (
     *     path="/api/v2/review/index",
     *     tags={"CMS Review"},
     *     summary="CMS danh sách review",
     *     security={{"bearerAuth":{}}},
     *     operationId="v2/review/index",
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
     *          name="rate[]",
     *          required=false,
     *          description="Filter theo rate",
     *          @OA\Schema(
     *            type="array",
     *            @OA\Items(type="integer"),
     *          )
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          name="room_id[]",
     *          required=false,
     *          description="Filter theo id room hoặc tour",
     *          @OA\Schema(
     *            type="array",
     *            @OA\Items(type="integer"),
     *          )
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          name="user_id[]",
     *          required=false,
     *          description="Filter user",
     *          @OA\Schema(
     *            type="array",
     *            @OA\Items(type="integer"),
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
    public function listReview(Request $request)
    {
        try {
            $reviews = $this->reviewV2Service->listReview($request);

            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.success'),
                'data' => $reviews
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
     * @OA\Get (
     *     path="/api/v2/review/show/{id}",
     *     tags={"CMS Review"},
     *     summary="CMS Chi tiết Review",
     *     security={{"bearerAuth":{}}},
     *     operationId="v2/review/show/{id}",
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
     *          description="ID Review",
     *          @OA\Schema(
     *            type="integer",
     *            example="",
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
    public function detailReview($id): JsonResponse
    {
        try {
            $review = $this->reviewV2Service->detailReview($id);

            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.success'),
                'data' => $review
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
     * @OA\Delete  (
     *     path="/api/v2/review/multiple-delete",
     *     tags={"CMS Review"},
     *     summary="CMS Xóa Review",
     *     security={{"bearerAuth":{}}},
     *     operationId="v2/review/multiple-delete",
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
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="ids",
     *                  type="array",
     *                  description="Array ids delete",
     *                  @OA\Items(
     *                      type="integer",
     *                      example=8,
     *                  )
     *              ),
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
    public function multipleDeleteReviews(Request $request)
    {
        try {
            DB::beginTransaction();
            $dataDelete = $request->ids;

            $review = $this->reviewV2Service->deleteMultipleReviews($dataDelete);

            DB::commit();
            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.success'),
                'data' => $review
            ], Constant::SUCCESS_CODE);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => Constant::FALSE_CODE,
                'message' => $th->getMessage(),
                'data' => []
            ], Constant::INTERNAL_SV_ERROR_CODE);
        }
    }

}
