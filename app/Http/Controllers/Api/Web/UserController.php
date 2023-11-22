<?php

namespace App\Http\Controllers\Api\Web;

use App\Models\User;
use App\Enums\Constant;
use Illuminate\Http\Request;
use App\Http\Traits\AuthTrait;
use App\Services\Web\UserService;
use Illuminate\Http\JsonResponse;
use App\Services\User\AuthService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\FileUploadServices\FileService;

class UserController extends Controller
{

    use AuthTrait;

    private $user;
    private $userService;
    private $fileService;
    private $authService;

    public function __construct(
        User $user,
        UserService $userService,
        FileService $fileService,
        AuthService $authService
    ) {
        $this->user = $user;
        $this->userService = $userService;
        $this->fileService = $fileService;
        $this->authService = $authService;

    }

    /**
     * @author Quangnh
     * @OA\Get (
     *     path="/api/user/show/{id}",
     *     tags={"User"},
     *     summary="Chi tiết khách hàng",
     *     security={{"bearerAuth":{}}},
     *     operationId="/user/show",
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
            $user = $this->user->select('id', 'email', 'display_name', 'phone_number', 'role_id', 'avatar', 'detail_address')->where('id', $id)->first();

            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.success'),
                'data' => $user
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
     *     path="/api/user/update/{id}",
     *     tags={"User"},
     *     summary="edit user",
     *     security={{"bearerAuth":{}}},
     *     operationId="user/update",
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
     *                          @OA\Property(property="display_name", type="string"),
     *                          @OA\Property(property="phone_number", type="string"),
     *                          @OA\Property(property="detail_address", type="string"),
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
    public function update(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $id = $request->id;
            $checkUser = $this->user->where('id', $id)->first();
            if (!isset($checkUser) || $checkUser->role_id !=2 ) {
                DB::rollBack();
                return response()->json([
                    'status' => Constant::BAD_REQUEST_CODE,
                    'message' => trans('messages.errors.staff.not_found'),
                    'data' => []
                ], Constant::BAD_REQUEST_CODE);
            }

            if ($request->image_delete == 'true') {
                $this->fileService->deleteImage($checkUser->avatar);
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

            $data = $this->getUserRequest($request);

            $staff = $this->userService->updateUser($data, $id);

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

    public function getUserRequest($request): array
    {
        $data = [];

        if (!$request->image_delete || $request->image_delete == 'true') {
            $data['avatar'] = $request->avatar;
        }

        if ($request->email) {
            $data['email'] = $request->email;
        }

        $data['display_name'] = $request->display_name;
        $data['detail_address'] = $request->detail_address;
        $data['image_delete'] = $request->image_delete;
        $data['phone_number'] = $request->phone_number;
        $data['role_id'] = 2;
        $data['status'] = 1;

        return $data;
    }

    /**
     * @OA\Post (
     *     path="/api/user/updatePs/{id}",
     *     tags={"User"},
     *     summary="edit password user",
     *     security={{"bearerAuth":{}}},
     *     operationId="user/updatePs",
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
     *          description="ID user",
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
     *                          @OA\Property(property="newPassword", type="string"),
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
    public function updatePs(Request $request): JsonResponse
    {
        try {

            DB::beginTransaction();
            $id = $request->id;
            $checkUser = $this->user->where('id', $id)->first();
            if (!isset($checkUser)) {

                DB::rollBack();
                return response()->json([
                    'status' => Constant::BAD_REQUEST_CODE,
                    'message' => trans('messages.errors.customer.not_found'),
                    'data' => []
                ], Constant::BAD_REQUEST_CODE);
            }

            $credentials = [
                'email' => $checkUser->email,
                'password' => $request->password
            ];

            if (!Hash::check($request->password, $checkUser->password)){
                return response()->json([
                    'status' => Constant::BAD_REQUEST_CODE,
                    'errorCode' => 'E_UC2_3',
                    'message' => trans('messages.errors.users.password_not_correct'),
                    'data' => []
                ], Constant::BAD_REQUEST_CODE);
            }
            $user = $this->authService->changePassword($id, $request->newPassword);

            DB::commit();
            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.customer.edit_password'),
                'data' => $user
            ], Constant::SUCCESS_CODE);
        } catch (\Throwable $th) {

            DB::rollBack();
            return response()->json([
                'status' => Constant::FALSE_CODE,
                'message' => $th->getMessage()
            ], Constant::INTERNAL_SV_ERROR_CODE);
        }
    }
}
