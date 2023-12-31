<?php

namespace App\Http\Controllers\Api\Web;

use App\Models\User;
use App\Enums\Constant;
use App\Notifications\VerifyCodeEmail;
use Illuminate\Contracts\Auth\Guard;
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

    /**
     * @author Quangnh
     * @OA\Post (
     *     path="/api/user/send-code",
     *     tags={"Tài khoản"},
     *     summary="Gửi mã code quên mật khẩu",
     *     security={{"bearerAuth":{}}},
     *     operationId="user/send-code",
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
     *              @OA\Property(property="email", type="string"),
     *          @OA\Examples(
     *              summary="Examples",
     *              example = "Examples",
     *              value = {
     *                  "email": "user@gmail.com",
     *                  },
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
    public function sendCodeForgotPassword(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = $this->checkUser($request);

            if (!$user) {
                return response()->json([
                    'status' => Constant::BAD_REQUEST_CODE,
                    'message' => 'This email does not exist!',
                    'data' => []
                ], Constant::BAD_REQUEST_CODE);
            }
            $randomCode = sprintf("%04d", mt_rand(1, 9999));

            // save code to DB
            $this->userService->saveCode($request->email, $randomCode);

            // send notify code
            $user->notify(new VerifyCodeEmail($randomCode, $request->email));

            DB::commit();

            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.users.forgot_password'),
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

    /**
     * @author Nampx
     * @OA\Post (
     *     path="/api/user/resend-code",
     *     tags={"Tài khoản"},
     *     summary="Gửi lại mã code",
     *     security={{"bearerAuth":{}}},
     *     operationId="user/resend-code",
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
     *              @OA\Property(property="email", type="string"),
     *          @OA\Examples(
     *              summary="Examples",
     *              example = "Examples",
     *              value = {
     *                  "email": "user@gmail.com",
     *                  },
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
    public function reSendCode(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = $this->checkUser($request);

            if (!$user) {
                return response()->json([
                    'status' => Constant::BAD_REQUEST_CODE,
                    'message' => 'This email does not exist!',
                    'data' => []
                ], Constant::BAD_REQUEST_CODE);
            }
            $randomCode = sprintf("%04d", mt_rand(1, 9999));

            // save code to DB
            $this->userService->saveCode($request->email, $randomCode);

            // send notify code
            $user->notify(new VerifyCodeEmail($randomCode, $request->email));

            DB::commit();

            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.users.resend_code'),
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

    /**
     * @author Quangnh
     * @OA\Post (
     *     path="/api/user/verify-code",
     *     tags={"Tài khoản"},
     *     summary="Quên mật khẩu",
     *     security={{"bearerAuth":{}}},
     *     operationId="user/verify-code",
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
     *              @OA\Property(property="email", type="string"),
     *              @OA\Property(property="code", type="string"),
     *          @OA\Examples(
     *              summary="Examples",
     *              example = "Examples",
     *              value = {
     *                  "email": "user@gmail.com",
     *                  "code": "1234",
     *                  },
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
    public function codeVerify(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // get code & email
            $getCode = $this->userService->getCode($request);

            if (!empty($getCode)) {

                DB::commit();

                return response()->json([
                    'status' => Constant::SUCCESS_CODE,
                    'message' => trans('messages.success.users.confirm_success'),
                    'data' => []
                ], Constant::SUCCESS_CODE);

            }

            DB::rollBack();

            return response()->json([
                'status' => Constant::BAD_REQUEST_CODE,
                'message' => 'Verify code failed!',
                'data' => []
            ], Constant::BAD_REQUEST_CODE);

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
     * @OA\Post (
     *     path="/api/user/reset-password",
     *     tags={"Tài khoản"},
     *     summary="Reset mật khẩu",
     *     security={{"bearerAuth":{}}},
     *     operationId="user/reset-password",
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
     *              @OA\Property(property="email", type="string"),
     *              @OA\Property(property="code", type="string"),
     *              @OA\Property(property="new_password", type="string"),
     *          @OA\Examples(
     *              summary="Examples",
     *              example = "Examples",
     *              value = {
     *                  "email": "user@gmail.com",
     *                  "code": "1234",
     *                  "new_password": "123456",
     *                  },
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
    public function resetPassword(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = $this->checkUser($request);

            if (!$user) {
                return response()->json([
                    'status' => Constant::BAD_REQUEST_CODE,
                    'message' => 'This email does not exist!',
                    'data' => []
                ], Constant::BAD_REQUEST_CODE);
            }

            // get code & email
            $getCode = $this->userService->getCodeToReset($request);

            if (!empty($getCode)) {
                $this->userService->newPassword($request->email, $request->new_password);

                $this->userService->deleteCode($request);

                DB::commit();

                return response()->json([
                    'status' => Constant::SUCCESS_CODE,
                    'message' => 'Update password successfully!',
                    'data' => []
                ], Constant::SUCCESS_CODE);
            }
            DB::rollBack();

            return response()->json([
                'status' => Constant::BAD_REQUEST_CODE,
                'message' => 'Verify code failed!',
                'data' => []
            ], Constant::BAD_REQUEST_CODE);

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
     * @return Guard
     * @author Quangnh
     */
    public function guard(): Guard
    {
        return Auth::guard();
    }

    public function checkUser($request)
    {
        return User::where('email', $request->email)->first();
    }

}
