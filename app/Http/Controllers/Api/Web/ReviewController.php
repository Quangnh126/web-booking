<?php


namespace App\Http\Controllers\Api\Web;


use App\Enums\Constant;
use App\Http\Controllers\Controller;
use App\Models\File;
use App\Services\FileUploadServices\FileService;
use App\Services\Web\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    private $reviewService;
    private $fileService;

    public function __construct(
        ReviewService $reviewService,
        FileService $fileService
    )
    {
        $this->reviewService = $reviewService;
        $this->fileService = $fileService;
    }

    /**
     * @OA\Post (
     *     path="/api/review/create",
     *     tags={"Review"},
     *     summary="Tạo Review",
     *     security={{"bearerAuth":{}}},
     *     operationId="review/create",
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
     *                          @OA\Property(property="user_id", type="integer"),
     *                          @OA\Property(property="room_id", type="string"),
     *                          @OA\Property(property="rate", type="double"),
     *                          @OA\Property(property="content", type="string"),
     *                          @OA\Property(property="images[]", type="string", format="binary"),
     *                         )
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
    public function createReview(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            if ($request->images) {
                $images = [];

                $totalImage = $this->checkTotalImageReview($request->images);

                if ($totalImage) {
                    DB::rollBack();
                    return response()->json([
                        'status' => Constant::BAD_REQUEST_CODE,
                        'errorCode' => 'E_UC4_1',
                        'message' => trans('messages.errors.review.image'),
                        'data' => []
                    ], Constant::BAD_REQUEST_CODE);
                }

                foreach ($request->images as $image) {
                    $pathNameReview = $this->fileService->getFilePath($image, Constant::PATH_REVIEW);
                    if (!$pathNameReview) {

                        DB::rollBack();
                        return response()->json([
                            'status' => Constant::BAD_REQUEST_CODE,
                            'errorCode' => 'E_UC4_1',
                            'message' => trans('messages.errors.image.not_available'),
                            'data' => []
                        ], Constant::BAD_REQUEST_CODE);
                    }
                    $images[] = $pathNameReview;
                }

            } else {
                $images = [];
            }

            $checkReview = $this->reviewService->checkReview($request->user_id, $request->room_id);
            if ($checkReview) {
                DB::rollBack();
                return response()->json([
                    'status' => Constant::BAD_REQUEST_CODE,
                    'message' => trans('messages.errors.review.check'),
                    'data' => []
                ], Constant::BAD_REQUEST_CODE);
            }

            $data = $this->getFormReview($request);

            $review = $this->reviewService->createReview($data);

            if (!empty($images)) {
                $this->fileService->storeFile($images, $review->id, File::$review);
            }

            DB::commit();
            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.review.create'),
                'data' => $review
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

    public function getFormReview($request): array
    {
        $data = [];

        $data['user_id'] = $request->user_id;
        $data['room_id'] = $request->room_id;
        $data['rate'] = $request->rate;
        $data['content'] = $request->content;

        return $data;
    }

    public function getImage($file, $type): bool|JsonResponse|string
    {
        if ($file) {
            $pathName = $this->fileService->getFilePath($file, $type);
            if (!$pathName) {

                DB::rollBack();
                return response()->json([
                    'status' => Constant::BAD_REQUEST_CODE,
                    'errorCode' => 'E_UC4_1',
                    'message' => trans('messages.errors.image.not_available'),
                ], Constant::BAD_REQUEST_CODE);
            }
        } else {
            $pathName = "";
        }
        return $pathName;
    }

    public function checkTotalImageReview($images, $bannerImage = null): bool
    {
        return ((count($images) ?? 0) + $bannerImage) > 5;
    }

}
