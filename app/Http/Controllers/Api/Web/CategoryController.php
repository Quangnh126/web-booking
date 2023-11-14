<?php


namespace App\Http\Controllers\Api\Web;


use App\Enums\Constant;
use App\Http\Controllers\Controller;
use App\Services\Web\CategoryService;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    private $categoryService;

    public function __construct(
        CategoryService $categoryService
    )
    {
        $this->categoryService = $categoryService;
    }

    /**
     * @OA\Get (
     *     path="/api/category/index",
     *     tags={"Category"},
     *     summary="Lấy thông tin thể loại",
     *     security={{"bearerAuth":{}}},
     *     operationId="category/index",
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
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *             @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Success."),
     *          )
     *     ),
     * )
     */
    public function listCategories(): JsonResponse
    {
        try {
            $category = $this->categoryService->listCategory();

            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.success'),
                'data' => $category
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
