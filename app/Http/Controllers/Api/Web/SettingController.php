<?php


namespace App\Http\Controllers\Api\Web;


use App\Enums\Constant;
use App\Http\Controllers\Controller;
use App\Services\Web\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    private $settingService;

    public function __construct(
        SettingService $settingService
    )
    {
        $this->settingService = $settingService;
    }

    /**
     * @OA\Get (
     *     path="/api/setting/contact",
     *     tags={"Setting"},
     *     summary="Lấy thông tin liên hệ",
     *     security={{"bearerAuth":{}}},
     *     operationId="setting/contact",
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
    public function getContact(): JsonResponse
    {
        try {
            $contact = $this->settingService->getContact();

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

}
