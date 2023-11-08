<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\User;
use App\Enums\Constant;
use Illuminate\Http\Request;
use App\Http\Traits\AuthTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\Admin\StaffV2Service;
use App\Services\Admin\CustomerService;
use App\Services\FileUploadServices\FileService;
use App\Http\Requests\Admin\CustomerRequest;


class CustomerController extends Controller
{
    use AuthTrait;

    private $user;
    private $customerService;
    private $fileService;

    public function __construct(
        User $user,
        CustomerService $customerService,
        FileService $fileService
    ) {
        $this->user = $user;
        $this->customerService = $customerService;
        $this->fileService = $fileService;
    }

    /**
     * @author Quangnh
     * @OA\Get (
     *     path="/api/v2/customer/index",
     *     tags={"CMS Customer"},
     *     summary="CMS danh sách khách hàng",
     *     security={{"bearerAuth":{}}},
     *     operationId="v2/customer/index",
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
    public function listCustomer (Request $request) { {
        try {
                $user = $this->getCurrentLoggedIn();
                $customer = $this->customerService->listCustomer($request);
                    return response()->json([
                        'status' => Constant::SUCCESS_CODE,
                        'message' => trans('messages.success.success'),
                        'data' => $customer
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

    /**
     * @OA\Post (
     *     path="/api/v2/customer/updateStatus/{id}",
     *     tags={"CMS Customer"},
     *     summary="CMS edit status customer",
     *     security={{"bearerAuth":{}}},
     *     operationId="v2/customer/updateStatus",
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
     *          description="ID customer",
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
     *                          @OA\Property(property="status", type="interger"),
     *                        )
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
    public function updateStatus(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $id = $request->id;
            $checkCustomer = $this->user->where('id', $id)->first();
            if (!isset($checkCustomer)) {

                DB::rollBack();
                return response()->json([
                    'status' => Constant::BAD_REQUEST_CODE,
                    'message' => trans('messages.errors.customer.not_found'),
                    'data' => []
                ], Constant::BAD_REQUEST_CODE);
            }

            $data = $request['status'];
            $customer = $this->customerService->updateCustomer($data, $id);

            DB::commit();
            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.customer.edit'),
                'data' => $customer
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
     * @OA\Delete  (
     *     path="/api/v2/customer/multiple-delete",
     *     tags={"CMS Customer"},
     *     summary="CMS xóa nhiều nhân viên",
     *     security={{"bearerAuth":{}}},
     *     operationId="v2/customer/multiple-delete",
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
    public function multipleDelete(Request $request): JsonResponse
    {
        try {

            DB::beginTransaction();
            $ids_delete = (array)$request->ids;
            $check_id_not_exist = $this->customerService->checkIdNotExist($ids_delete);

            if (!empty($check_id_not_exist)) {
                DB::rollBack();

                return response()->json([
                    'status' => Constant::BAD_REQUEST_CODE,
                    'message' => trans('messages.errors.customer.not_found', ['message_error' => $check_id_not_exist]),
                    'data' => []
                ], Constant::BAD_REQUEST_CODE);
            }

            $this->customerService->deleteMultipleCustomer($ids_delete);

            DB::commit();
            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.customer.delete'),
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

    public function getCustomerRequest($request): array
    {
        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        }

        if ($request->avatar) {
            $data['avatar'] = $request->avatar;
        }

        if ($request->email) {
            $data['email'] = $request->email;
        }

        $data['display_name'] = $request->display_name;
        $data['image_delete'] = $request->image_delete;
        $data['phone_number'] = $request->phone_number;
        $data['role_id'] = $request->role_id;
        $data['status'] = 1;

        return $data;
    }
}
