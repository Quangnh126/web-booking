<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Web\AuthRequest;
use App\Models\Role;
use App\Models\User;
use App\Enums\Constant;
use Illuminate\Http\Request;
use App\Http\Traits\AuthTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\User\AuthService;
use App\Services\FileUploadServices\FileService;

class AuthController extends Controller
{
    private $user;
    private $authService;
    private $fileService;

    public function __construct(
        User $user,
        AuthService $authService,
        FileService $fileService
    ) {
        $this->user = $user;
        $this->authService = $authService;
        $this->fileService = $fileService;
    }

    /**
     * @author Quangnh
     * @OA\Post (
     *     path="/api/auth/login",
     *     tags={"Tài khoản"},
     *     summary="Đăng nhập User",
     *     operationId="users/login/user",
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
     *              @OA\Property(property="password", type="string"),
     *              @OA\Property(property="device_token", type="string"),
     *          @OA\Examples(
     *              summary="Examples",
     *              example = "Examples",
     *              value = {
     *                  "email": "user@gmail.com",
     *                  "password": "123123",
     *                  "device_token": "xxx111xxx",
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
    public function loginUser(Request $request): JsonResponse
    {
        try {
            $user = $this->user->ofEmail($request->email)
                ->ofRole(User::$user)
                ->selectRaw('id, display_name, email, phone_number, avatar, role_id, status, verify')
                ->first();

            if (!$user) {
                return response()->json([
                    'status' => Constant::BAD_REQUEST_CODE,
                    'errorCode' => 'E_UC2_1',
                    'message' => trans('messages.errors.users.email_not_found'),
                    'data' => []
                ], Constant::BAD_REQUEST_CODE);
            }

            $credentials = [
                'email' => $request->email,
                'password' => $request->password
            ];

            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'status' => Constant::BAD_REQUEST_CODE,
                    'errorCode' => 'E_UC2_3',
                    'message' => trans('messages.errors.users.password_not_correct'),
                    'data' => []
                ], Constant::BAD_REQUEST_CODE);
            }

            if ($user && $user->status == User::$inactive){
                return response()->json([
                    'status' => Constant::BAD_REQUEST_CODE,
                    'errorCode' => 'E_UC2_1',
                    'message' => trans('messages.errors.users.account_not_active'),
                    'data' => []
                ], Constant::BAD_REQUEST_CODE);
            }

            // xoa token cu
//            $user->tokens()->delete();

            $user->update([
                'device_token' => $request->device_token
            ]);

            $data = [];
            $data['user'] = $user;
            $data['isVerify'] = $user->verify;
            $data['role'] = $user->role_id == 2 ? Role::$user : Role::$admin;
            $data['token'] = $user->createToken($request->device_token)->plainTextToken;

//            ($user->verify == 1) ? $data['isVerify'] = $user->verify : $data = ['isVerify' => $user->verify];

            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.users.login_success'),
                'data' => $data
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
     * @OA\Post (
     *     path="/api/register",
     *     tags={"Tài khoản"},
     *     summary="Đăng kí tài khoản User",
     *     security={{"bearerAuth":{}}},
     *     operationId="/register",
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
     *                          @OA\Property(property="detail_address", type="string"),
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
    public function register(AuthRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $data = $this->getCustomerRequest($request);

            $customer = $this->authService->register($data);

            DB::commit();
            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.customer.create'),
                'data' => $customer
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

        if ($request->email) {
            $data['email'] = $request->email;
        }

        $data['display_name'] = $request->display_name;
        $data['detail_address'] = $request->detail_address;
        $data['phone_number'] = $request->phone_number;
        $data['role_id'] = 2;
        $data['status'] = 1;

        return $data;
    }

}
