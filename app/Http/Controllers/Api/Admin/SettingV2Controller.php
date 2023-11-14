<?php


namespace App\Http\Controllers\Api\Admin;


use App\Enums\Constant;
use App\Http\Controllers\Controller;
use App\Services\Admin\SettingV2Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingV2Controller extends Controller
{
    private $settingV2Service;

    public function __construct(
        SettingV2Service $settingV2Service
    )
    {
        $this->settingV2Service = $settingV2Service;
    }

    /**
     * @OA\Get (
     *     path="/api/v2/setting/contact",
     *     tags={"CMS Setting"},
     *     summary="CMS Lấy thông tin liên hệ",
     *     security={{"bearerAuth":{}}},
     *     operationId="v2/setting/contact",
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
    public function getContact()
    {
        try {
            $contact = $this->settingV2Service->getContact();

            $data = json_decode($contact->value);

            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.success'),
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
     * @OA\Post (
     *     path="/api/v2/setting/create-contact",
     *     tags={"CMS Setting"},
     *     summary="CMS Tạo thông tin liên hệ",
     *     security={{"bearerAuth":{}}},
     *     operationId="v2/setting/create-contact",
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
     *              @OA\Property(property="phone_number", type="string"),
     *              @OA\Property(property="email", type="string"),
     *              @OA\Property(property="facebook", type="string"),
     *              @OA\Property(property="zalo", type="string"),
     *          @OA\Examples(
     *              summary="Examples",
     *              example = "Examples",
     *              value = {
     *                  "phone_number": "0987654321",
     *                  "email": "contact.web.booking@gmail.com",
     *                  "facebook": "fb.com",
     *                  "zalo": "zalo.com",
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
    public function createContact(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = $this->getCurrentLoggedIn();

            if ($user->can('viewAny', $user)){

                $contact = $this->getContactRequest($request);

                $this->settingV2Service->updateOrCreate($contact);

                DB::commit();
                return response()->json([
                    'status' => Constant::SUCCESS_CODE,
                    'message' => trans('messages.success.success'),
                    'data' => 'Success'
                ], Constant::SUCCESS_CODE);

            } else {
                return response()->json([
                    'status' => Constant::BAD_REQUEST_CODE,
                    'message' => trans('messages.errors.users.not_permission'),
                    'data' => []
                ], Constant::BAD_REQUEST_CODE);
            }
        }catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => Constant::FALSE_CODE,
                'message' => $th->getMessage()
            ], Constant::INTERNAL_SV_ERROR_CODE);
        }

    }

    public function getContactRequest($request): array
    {
        return [
            'phone_number' => $request->phone_number,
            'email' => $request->email ?? "",
            'facebook' => $request->facebook ?? "",
            'zalo' => $request->zalo ?? ""
        ];
    }

}
