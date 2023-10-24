<?php


namespace App\Http\Controllers\Api\Admin;


use App\Enums\Constant;
use App\Http\Traits\AuthTrait;
use App\Models\Role;
use App\Models\User;
use App\Services\Admin\UserV2Service;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserV2Controller
{
    use AuthTrait;

    private $userV2Service;
    private $user;

    public function __construct(
        UserV2Service $userV2Service,
        User $user
    )
    {
        $this->userV2Service = $userV2Service;
        $this->user = $user;
    }

    /**
     * @author Nampx
     * @OA\Post (
     *     path="/api/v2/auth/login",
     *     tags={"CMS Tài khoản"},
     *     summary="Đăng nhập Admin/Staff",
     *     operationId="v2/users/login/admin",
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
     *          @OA\Examples(
     *              summary="Examples",
     *              example = "Examples",
     *              value = {
     *                  "email": "admin@gmail.com",
     *                  "password": "123123",
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
    public function loginAdmin(Request $request): JsonResponse
    {
        try {
            $staff = $this->user->ofEmail($request->email)
                ->whereIn('role_id', [User::$admin, User::$staff])
                ->first();

            if ($staff) {
                if ($staff->status == 0) {
                    return response()->json([
                        'status' => Constant::BAD_REQUEST_CODE,
                        'errorCode' => 'E_UC2_2',
                        'message' => trans('messages.errors.users.account_not_active'),
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
                    ], Constant::BAD_REQUEST_CODE);
                }

                // xoa token cu
//                $staff->tokens()->delete();

                $data = [];
                $data['id'] = $staff->id;
                $data['display_name'] = $staff->fullname;
                $data['avatar'] = $staff->avatar;
                $data['role'] = ($staff->role_id == 1) ? Role::$admin : Role::$staff;
                $data['token'] = $staff->createToken("API TOKEN")->plainTextToken;

                return response()->json([
                    'status' => Constant::SUCCESS_CODE,
                    'message' => trans('messages.success.users.login_success'),
                    'data' => $data
                ], Constant::SUCCESS_CODE);
            } else {
                $store = $this->store->ofEmail($request->email)->where('is_deleted', Store::$not_deleted)
                    ->first();

                if ($store) {
                    if (!Hash::check($request->password, $store->password)) {
                        return response()->json([
                            'status' => Constant::BAD_REQUEST_CODE,
                            'errorCode' => 'E_UC2_3',
                            'message' => trans('messages.errors.users.password_not_correct'),
                        ], Constant::BAD_REQUEST_CODE);
                    }

                    // xoa token cu
//                    $store->tokens()->delete();

                    $data = [];
                    $data['id'] = $store->id;
                    $data['display_name'] = $store->name;
                    $data['avatar'] = $store->logo;
                    $data['role'] = Role::$store;
                    $data['token'] = $store->createToken("API TOKEN")->plainTextToken;

                    return response()->json([
                        'status' => Constant::SUCCESS_CODE,
                        'message' => trans('messages.success.users.login_success'),
                        'data' => $data,
                    ], Constant::SUCCESS_CODE);
                }
            }

            return response()->json([
                'status' => Constant::BAD_REQUEST_CODE,
                'errorCode' => 'E_UC2_1',
                'message' => trans('messages.errors.users.email_not_found'),
                'data' => []
            ], Constant::BAD_REQUEST_CODE);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => Constant::FALSE_CODE,
                'message' => $th->getMessage()
            ], Constant::INTERNAL_SV_ERROR_CODE);
        }
    }

}
