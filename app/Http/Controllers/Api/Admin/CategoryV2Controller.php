<?php


namespace App\Http\Controllers\Api\Admin;


use App\Http\Traits\AuthTrait;
use App\Models\Category;
use App\Services\Admin\CategoryV2Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Enums\Constant;

class CategoryV2Controller
{
    use AuthTrait;

    private $category;
    private $categoryV2Service;

    public function __construct(
        Category $category,
        CategoryV2Service $categoryV2Service
    )
    {
        $this->category = $category;
        $this->categoryV2Service = $categoryV2Service;
    }

    /**
     * @author Quangnh
     * @OA\Get (
     *     path="/api/v2/category/index",
     *     tags={"CMS Category"},
     *     summary="CMS danh sách thể loại",
     *     security={{"bearerAuth":{}}},
     *     operationId="v2/category/index",
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
     *          description="Tìm kiếm theo tên danh mục",
     *          @OA\Schema(
     *            type="string",
     *            example="1 người",
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
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $this->getCurrentLoggedIn();
            if ($user->can('viewAny', $user)) {
                $category = $this->categoryV2Service->listCategory($request);

                return response()->json([
                    'status' => Constant::SUCCESS_CODE,
                    'message' => trans('messages.success.success'),
                    'data' => $category
                ], Constant::SUCCESS_CODE);
            } else {
                return response()->json([
                    'status' => Constant::BAD_REQUEST_CODE,
                    'message' => trans('messages.errors.users.not_permission'),
                    'data' => []
                ], Constant::BAD_REQUEST_CODE);
            }

        } catch (\Throwable $th) {

            return response()->json([
                'status' => Constant::FALSE_CODE,
                'message' => $th->getMessage(),
                'data' => []
            ], Constant::INTERNAL_SV_ERROR_CODE);

        }
    }

    /**
     * @OA\Post (
     *     path="/api/v2/category/create",
     *     tags={"CMS Category"},
     *     summary="CMS tạo danh mục",
     *     security={{"bearerAuth":{}}},
     *     operationId="v2/category/create",
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
     *                          @OA\Property(property="name", type="string"),
     *                          @OA\Property(property="number", type="string"),
     *                          @OA\Property(property="description", type="string"),
     *                       )
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
    public function createCategory(Request $request): JsonResponse
    {
        try {
            $user = $this->getCurrentLoggedIn();
            if ($user->can('viewAny', $user)) {
                DB::beginTransaction();

                $array_data = $this->getRequestCategory($request);
                $checkCategoryExist = $this->categoryV2Service->checkNameExist($array_data);

                if ($checkCategoryExist) {
                    $category = $this->categoryV2Service->createCategory($array_data);
                } else {
                    return response()->json([
                        'status' => Constant::FORBIDDEN_CODE,
                        'message' => trans('messages.errors.category.exist'),
                        'data' => [],
                    ], Constant::FORBIDDEN_CODE);
                }

                DB::commit();
                return response()->json([
                    'status' => Constant::SUCCESS_CODE,
                    'message' => trans('messages.success.success'),
                    'data' => $category
                ], Constant::SUCCESS_CODE);
            } else {
                return response()->json([
                    'status' => Constant::BAD_REQUEST_CODE,
                    'message' => trans('messages.errors.users.not_permission'),
                    'data' => []
                ], Constant::BAD_REQUEST_CODE);
            }

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
     *     path="/api/v2/category/show/{id}",
     *     tags={"CMS Category"},
     *     summary="CMS chi tiết danh mục",
     *     security={{"bearerAuth":{}}},
     *     operationId="v2/category/show",
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
     *          description="ID danh mục",
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
    public function showCategory(int $id): JsonResponse
    {
        try {
            $category = $this->category->select('id', 'name', 'description', 'number')->where('id', $id)->first();

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

    /**
     * @OA\Post (
     *     path="/api/v2/category/update/{id}",
     *     tags={"CMS Category"},
     *     summary="CMS chỉnh sửa danh mục",
     *     security={{"bearerAuth":{}}},
     *     operationId="v2/category/update",
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
     *          description="ID Category",
     *          @OA\Schema(
     *            type="integer",
     *            example="",
     *          )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 allOf={
     *                     @OA\Schema(
     *                          @OA\Property(property="name", type="string"),
     *                          @OA\Property(property="number", type="string"),
     *                          @OA\Property(property="description", type="string"),
     *                         )
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
    public function updateCategory(Request $request): JsonResponse
    {
        try {
            $user = $this->getCurrentLoggedIn();
            if ($user->can('viewAny', $user)) {
                DB::beginTransaction();

                $id = $request->id;
                $array_data = $this->getRequestCategory($request);
                $category = $this->categoryV2Service->updateCategory($array_data, $id);

                DB::commit();
                return response()->json([
                    'status' => Constant::SUCCESS_CODE,
                    'message' => trans('messages.success.success'),
                    'data' => $category
                ], Constant::SUCCESS_CODE);
            } else {
                return response()->json([
                    'status' => Constant::BAD_REQUEST_CODE,
                    'message' => trans('messages.errors.users.not_permission'),
                    'data' => []
                ], Constant::BAD_REQUEST_CODE);
            }

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
     *     path="/api/v2/category/multiple-delete",
     *     tags={"CMS Category"},
     *     summary="CMS xóa nhiều danh mục",
     *     security={{"bearerAuth":{}}},
     *     operationId="v2/category/multiple-delete",
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
    public function multipleDeleteCategory(Request $request): JsonResponse
    {
        try {

            DB::beginTransaction();
            $ids_delete = (array)$request->ids;

            $this->categoryV2Service->deleteMultipleCategory($ids_delete);

            DB::commit();
            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.category.delete'),
                'data' => []

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

    public function getRequestCategory($request): array
    {
        if ($request->name) {
            $data['name'] = $request->name;
        }

        $data['number'] = $request->number;
        $data['description'] = $request->description;

        return $data;
    }

}
