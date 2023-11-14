<?php


namespace App\Http\Controllers\Api\Admin;


use App\Enums\Constant;
use App\Http\Traits\AuthTrait;
use App\Models\File;
use App\Models\Room;
use App\Services\Admin\RoomV2Service;
use App\Services\FileUploadServices\FileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomV2Controller
{
    use AuthTrait;

    private $room;
    private $roomV2Service;
    private $file;
    private $fileService;

    public function __construct(
        Room $room,
        RoomV2Service $roomV2Service,
        File $file,
        FileService $fileService
    )
    {
        $this->room = $room;
        $this->roomV2Service = $roomV2Service;
        $this->file = $file;
        $this->fileService = $fileService;
    }

    /**
     * @OA\Get (
     *     path="/api/v2/room/index",
     *     tags={"CMS Room"},
     *     summary="CMS danh sách room",
     *     security={{"bearerAuth":{}}},
     *     operationId="v2/room/index",
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
     *          description="Tìm kiếm theo tên sự kiện, mô tả",
     *          @OA\Schema(
     *            type="string",
     *            example="",
     *          )
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          name="status[]",
     *          required=false,
     *          description="Filter trạng thái",
     *          @OA\Schema(
     *            type="array",
     *            @OA\Items(type="integer"),
     *          )
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          name="type[]",
     *          required=false,
     *          description="Filter thể loại phòng hoặc tour",
     *          @OA\Schema(
     *            type="array",
     *            @OA\Items(type="integer"),
     *          )
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          name="type_room[]",
     *          required=false,
     *          description="Filter room hoặc tour",
     *          @OA\Schema(
     *            type="array",
     *            @OA\Items(type="string"),
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
    public function index(Request $request): JsonResponse
    {
        try {
            $room = $this->roomV2Service->listRoom($request);

            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.success'),
                'data' => $room
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
     *     path="/api/v2/room/create-room",
     *     tags={"CMS Room"},
     *     summary="CMS Tạo room",
     *     security={{"bearerAuth":{}}},
     *     operationId="v2/room/create-room",
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
     *                          @OA\Property(property="name", type="string"),
     *                          @OA\Property(property="description", type="string"),
     *                          @OA\Property(property="type", type="string", example="1"),
     *                          @OA\Property(property="cost", type="string", example="1000000"),
     *                          @OA\Property(property="start_date", type="string", example=""),
     *                          @OA\Property(property="end_date", type="string", example=""),
     *                          @OA\Property(property="logo", type="string", format="binary"),
     *                          @OA\Property(property="banner[]", type="string", format="binary"),
     *                          @OA\Property(property="status", type="string", example="1"),
     *                          @OA\Property(property="type_room", type="string", example="room"),
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
    public function createRoom(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $request->logo = $this->getImage($request->logo, Constant::PATH_ROOM);

            if ($request->banner) {
                $images = [];

                $totalBanner = $this->checkTotalBanner($request->banner);

                if ($totalBanner) {
                    DB::rollBack();
                    return response()->json([
                        'status' => Constant::BAD_REQUEST_CODE,
                        'errorCode' => 'E_UC4_1',
                        'message' => trans('messages.errors.event.banner'),
                        'data' => []
                    ], Constant::BAD_REQUEST_CODE);
                }
                foreach ($request->banner as $image) {
                    $pathNameRoom = $this->fileService->getFilePath($image, Constant::PATH_ROOM);
                    if (!$pathNameRoom) {

                        DB::rollBack();
                        return response()->json([
                            'status' => Constant::BAD_REQUEST_CODE,
                            'errorCode' => 'E_UC4_1',
                            'message' => trans('messages.errors.image.not_available'),
                            'data' => []
                        ], Constant::BAD_REQUEST_CODE);
                    }
                    $images[] = $pathNameRoom;
                }
            } else {

                $images = [];
            }

            $roomRq = $this->getRoomRequest($request);

            $room = $this->roomV2Service->createRoom($roomRq);

            if (!empty($images)) {
                $this->fileService->storeFile($images, $room->id, File::$room);
            }

            DB::commit();
            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.room.create'),
                'data' => $room
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
     * @OA\Post (
     *     path="/api/v2/room/create-tour",
     *     tags={"CMS Room"},
     *     summary="CMS Tạo Tour",
     *     security={{"bearerAuth":{}}},
     *     operationId="v2/room/create-tour",
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
     *                          @OA\Property(property="name", type="string"),
     *                          @OA\Property(property="description", type="string"),
     *                          @OA\Property(property="type", type="string", example="1"),
     *                          @OA\Property(property="cost", type="string", example="1000000"),
     *                          @OA\Property(property="start_date", type="string", example=""),
     *                          @OA\Property(property="end_date", type="string", example=""),
     *                          @OA\Property(property="logo", type="string", format="binary"),
     *                          @OA\Property(property="banner[]", type="string", format="binary"),
     *                          @OA\Property(property="status", type="string", example="1"),
     *                          @OA\Property(property="type_room", type="string", example="tour"),
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
    public function createTour(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $request->logo = $this->getImage($request->logo, Constant::PATH_TOUR);

            if ($request->banner) {
                $images = [];

                $totalBanner = $this->checkTotalBanner($request->banner);

                if ($totalBanner) {
                    DB::rollBack();
                    return response()->json([
                        'status' => Constant::BAD_REQUEST_CODE,
                        'errorCode' => 'E_UC4_1',
                        'message' => trans('messages.errors.room.banner'),
                        'data' => []
                    ], Constant::BAD_REQUEST_CODE);
                }
                foreach ($request->banner as $image) {
                    $pathNameTour = $this->fileService->getFilePath($image, Constant::PATH_TOUR);
                    if (!$pathNameTour) {

                        DB::rollBack();
                        return response()->json([
                            'status' => Constant::BAD_REQUEST_CODE,
                            'errorCode' => 'E_UC4_1',
                            'message' => trans('messages.errors.image.not_available'),
                            'data' => []
                        ], Constant::BAD_REQUEST_CODE);
                    }
                    $images[] = $pathNameTour;
                }
            } else {

                $images = [];
            }

            $tourRq = $this->getRoomRequest($request);

            $tour = $this->roomV2Service->createRoom($tourRq);

            if (!empty($images)) {
                $this->fileService->storeFile($images, $tour->id, File::$room);
            }

            DB::commit();
            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.room.create'),
                'data' => $tour
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
     * @OA\Get (
     *     path="/api/v2/room/show/{id}",
     *     tags={"CMS Room"},
     *     summary="CMS Chi tiết Room, Tour",
     *     security={{"bearerAuth":{}}},
     *     operationId="v2/room/show/{id}",
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
     *          required=false,
     *          description="ID Room, Tour",
     *          @OA\Schema(
     *            type="integer",
     *            example="",
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
    public function show($id): JsonResponse
    {
        try {
            $room = $this->room->with('banner', 'categories')
                ->select('id', 'name', 'description', 'type', 'logo', 'cost', 'start_date', 'end_date', 'status', 'type_room')
                ->where('id', $id)->first();

            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.success'),
                'data' => $room
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
     *     path="/api/v2/room/update/{id}",
     *     tags={"CMS Room"},
     *     summary="CMS edit room, tour",
     *     security={{"bearerAuth":{}}},
     *     operationId="v2/room/update",
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
     *          description="ID Room, Tour",
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
     *                          @OA\Property(property="name", type="string"),
     *                          @OA\Property(property="description", type="string"),
     *                          @OA\Property(property="type", type="string"),
     *                          @OA\Property(property="cost", type="string"),
     *                          @OA\Property(property="start_date", type="string", example=""),
     *                          @OA\Property(property="end_date", type="string", example=""),
     *                          @OA\Property(property="logo", type="string", format="binary"),
     *                          @OA\Property(property="logo_delete", type="boolean"),
     *                          @OA\Property(property="status", type="string", example="1"),
     *                          @OA\Property(property="type_room", type="string", example="room"),
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
    public function update(Request $request, $id): JsonResponse
    {
        try {
            DB::beginTransaction();
            if ($request->logo_delete){
                $this->roomV2Service->deleteLogo($request->logo_delete, $id);
                $request->logo = $this->getImage($request->logo, $request->type_room);
            }

            $roomRq = $this->getRoomRequest($request);

            $room = $this->roomV2Service->updateRoom($roomRq, $id);

            DB::commit();
            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.room.create'),
                'data' => $room
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
     * @OA\Delete  (
     *     path="/api/v2/room/multiple-delete",
     *     tags={"CMS Room"},
     *     summary="CMS Xóa Room, Tour",
     *     security={{"bearerAuth":{}}},
     *     operationId="v2/room/multiple-delete",
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
    public function multipleDelete(Request $request): JsonResponse
    {
        try {

            DB::beginTransaction();
            $ids_delete = (array)$request->ids;

            $this->roomV2Service->deleteMultiple($ids_delete);

            DB::commit();
            return response()->json([
                'status' => Constant::SUCCESS_CODE,
                'message' => trans('messages.success.success'),
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

    public function getRoomRequest($request): array
    {
        $data = [];

        if ($request->logo) {
            $data['logo'] = $request->logo;
        }

        $data['name'] = $request->name;
        $data['description'] = $request->description;
        $data['type'] = $request->type;
        $data['cost'] = $request->cost;
        $data['start_date'] = $request->start_date ?: null;
        $data['end_date'] = $request->start_date ?: null;
        $data['status'] = 1;
        $data['type_room'] = $request->type_room;

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

    public function checkTotalBanner($banners, $bannerCurrent = null): bool
    {
        return ((count($banners) ?? 0) + $bannerCurrent) > 5;
    }

}
