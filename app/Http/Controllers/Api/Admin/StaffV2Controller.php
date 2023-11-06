<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\Constant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StaffV2Request;
use App\Http\Traits\AuthTrait;
use App\Models\User;
use App\Services\Admin\StaffV2Service;
use App\Services\FileUploadServices\FileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffV2Controller extends Controller
{
    use AuthTrait;

    private $user;
    private $staffV2Service;
    private $fileService;

    public function __construct(
        User $user,
        StaffV2Service $staffV2Service,
        FileService $fileService
    )
    {
        $this->user = $user;
        $this->staffV2Service = $staffV2Service;
        $this->fileService = $fileService;
    }

    /**
     * @author Quangnh
     * @OA\Get (
     *     path="/api/v2/staff/index",
     *     tags={"CMS Staff"},
     *     summary="CMS danh sách nhân viên",
     *     security={{"bearerAuth":{}}},
     *     operationId="v2/staff/index",
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
     *          description="Tìm kiếm theo tài khoản và tên hiển thị",
     *          @OA\Schema(
     *            type="string",
     *            example="admin",
     *          )
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          name="role_id[]",
     *          required=false,
     *          description="1: admin, 3: staff",
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
    public function listStaff(StaffV2Request $request): JsonResponse
    {
        try {
            $user = $this->getCurrentLoggedIn();
            if ($user->can('viewStaff', $user)) {
                $staff = $this->staffV2Service->listStaff($request);

                return response()->json([
                    'status' => Constant::SUCCESS_CODE,
                    'message' => trans('messages.success.success'),
                    'data' => $staff
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
     * @OA\Post (
     *     path="/api/v2/staff/create",
     *     tags={"CMS Staff"},
     *     summary="CMS tạo nhân viên",
     *     security={{"bearerAuth":{}}},
     *     operationId="v2/staff/store",
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
     *                          @OA\Property(property="email", type="string"),
     *                          @OA\Property(property="password", type="string"),
     *                          @OA\Property(property="display_name", type="string"),
     *                          @OA\Property(property="phone_number", type="string"),
     *                          @OA\Property(property="role_id", type="string"),
     *                          @OA\Property(property="image_data", type="string", format="binary"),     *                     )
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
    public function store(StaffV2Request $request): JsonResponse
    {
        try {
            if ($request->password) {
                $data['password'] = bcrypt($request->password);
            }
            DB::beginTransaction();

            if ($request->image_data) {
                $pathName = $this->fileService->getFilePath($request->image_data, Constant::PATH_PROFILE);

                if (!$pathName) {
                    return response()->json([
                        'status' => Constant::BAD_REQUEST_CODE,
                        'errorCode' => 'E_UC4_1',
                        'message' => trans('messages.errors.image.not_available'),
                        'data' => []
                    ], Constant::BAD_REQUEST_CODE);
                }

                $request->avatar = $pathName;
            }

            $data = $this->getStaffRequest($request);

            $staff = $this->staffV2Service->createStaff($data);

            DB::commit();
            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.staff.create'),
                'data' => $staff
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

    /**
     * @author Quangnh
     * @OA\Get (
     *     path="/api/v2/staff/show/{id}",
     *     tags={"CMS Staff"},
     *     summary="CMS chi tiết nhân viên",
     *     security={{"bearerAuth":{}}},
     *     operationId="v2/staff/show",
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
     *          description="ID người dùng",
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
    public function show(int $id): JsonResponse
    {
        try {
            $staff = $this->user->select('id', 'email', 'display_name', 'phone_number', 'role_id', 'avatar')->where('id', $id)->first();

            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.success'),
                'data' => $staff
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
     *     path="/api/v2/staff/update/{id}",
     *     tags={"CMS Staff"},
     *     summary="CMS edit staff",
     *     security={{"bearerAuth":{}}},
     *     operationId="v2/staff/update",
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
     *          description="ID Staff",
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
     *                          @OA\Property(property="password", type="string"),
     *                          @OA\Property(property="display_name", type="string"),
     *                          @OA\Property(property="phone_number", type="string"),
     *                          @OA\Property(property="role_id", type="string"),
     *                          @OA\Property(property="image_delete", type="boolean"),
     *                          @OA\Property(property="image_data", type="string", format="binary"),
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
    public function update(StaffV2Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $id = $request->id;
            $checkStaff = $this->user->where('id', $id)->first();
            if (!isset($checkStaff)) {

                DB::rollBack();
                return response()->json([
                    'status' => Constant::BAD_REQUEST_CODE,
                    'message' => trans('messages.errors.staff.not_found'),
                    'data' => []
                ], Constant::BAD_REQUEST_CODE);
            }

            if ($request->image_data) {
                $this->fileService->deleteImage($checkStaff->avatar);
                $pathName = $this->fileService->getFilePath($request->image_data, Constant::PATH_PROFILE);

                if (!$pathName) {
                    return response()->json([
                        'status' => Constant::BAD_REQUEST_CODE,
                        'errorCode' => 'E_UC4_1',
                        'message' => trans('messages.errors.image.not_available'),
                        'data' => []
                    ], Constant::BAD_REQUEST_CODE);
                }

                $request->avatar = $pathName;
            }

            $data = $this->getStaffRequest($request);

            $staff = $this->staffV2Service->updateStaff($data, $id);

            DB::commit();
            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.staff.edit'),
                'data' => $staff
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
     *     path="/api/v2/staff/multiple-delete",
     *     tags={"CMS Staff"},
     *     summary="CMS xóa nhiều nhân viên",
     *     security={{"bearerAuth":{}}},
     *     operationId="v2/staff/multiple-delete",
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
    public function multipleDelete(StaffV2Request $request): JsonResponse
    {
        try {

            DB::beginTransaction();
            $ids_delete = (array)$request->ids;
            $check_id_not_exist = $this->staffV2Service->checkIdNotExist($ids_delete);

            if (!empty($check_id_not_exist)) {
                DB::rollBack();

                return response()->json([
                    'status' => Constant::BAD_REQUEST_CODE,
                    'message' => trans('messages.errors.staff.not_found', ['message_error' => $check_id_not_exist]),
                    'data' => []
                ], Constant::BAD_REQUEST_CODE);

            }

            $this->staffV2Service->deleteMultipleStaff($ids_delete);

            DB::commit();
            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.staff.delete'),
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

    public function getStaffRequest($request): array
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
