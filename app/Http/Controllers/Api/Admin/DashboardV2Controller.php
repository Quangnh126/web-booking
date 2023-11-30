<?php


namespace App\Http\Controllers\Api\Admin;


use App\Enums\Constant;
use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardV2Service;

class DashboardV2Controller extends Controller
{
    private $dashboardV2Service;

    public function __construct(DashboardV2Service $dashboardV2Service)
    {
        $this->dashboardV2Service = $dashboardV2Service;
    }

    /**
     * @author Quangnh
     * @OA\Get (
     *     path="/api/v2/dashboard/general",
     *     tags={"CMS Dashboard"},
     *     summary="CMS Dashboard",
     *     security={{"bearerAuth":{}}},
     *     operationId="v2/dashboard/general",
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
    public function general()
    {
        try {
            $dashboard = $this->dashboardV2Service->general();

            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.success'),
                'data' => $dashboard
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
