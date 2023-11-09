<?php

namespace App\Http\Controllers\Api\Web;

use App\Models\User;
use App\Enums\Constant;
use Illuminate\Http\Request;
use App\Http\Traits\AuthTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Services\Web\UserService;
use App\Http\Controllers\Controller;
use App\Services\FileUploadServices\FileService;

class UserController extends Controller
{

    use AuthTrait;

    private $user;
    private $userService;
    private $fileService;

    public function __construct(
        User $user,
        UserService $userService,
        FileService $fileService
    ) {
        $this->user = $user;
        $this->userService = $userService;
        $this->fileService = $fileService;
    }

    /**
     * @author Quangnh
     * @OA\Get (
     *     path="/api/user/show/{id}",
     *     tags={"CMS User"},
     *     summary="CMS chi tiết khách hàng",
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
            $user = $this->user->select('id', 'email', 'display_name', 'phone_number', 'role_id', 'avatar')->where('id', $id)->first();

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
     *     path="api/user/update/{id}",
     *     tags={"CMS User"},
     *     summary="CMS edit user",
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
    public function update(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $id = $request->id;
            $checkUser = $this->user->where('id', $id)->first();
            if (!isset($checkUser)) {

                DB::rollBack();
                return response()->json([
                    'status' => Constant::BAD_REQUEST_CODE,
                    'message' => trans('messages.errors.staff.not_found'),
                    'data' => []
                ], Constant::BAD_REQUEST_CODE);
            }

            if ($request->image_data) {
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