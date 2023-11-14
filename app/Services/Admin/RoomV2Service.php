<?php


namespace App\Services\Admin;


use App\Enums\Constant;
use App\Models\File;
use App\Models\Room;
use App\Services\FileUploadServices\FileService;

class RoomV2Service
{

    private $room;
    private $file;
    private $fileService;

    public function __construct(
        Room $room,
        File $file,
        FileService $fileService
    )
    {
        $this->room = $room;
        $this->file = $file;
        $this->fileService = $fileService;
    }

    public function listRoom($request)
    {
        $search = $request->search;
        $status = $request->status;
        $type = $request->type;
        $type_room = $request->type_room;
        $perPage = $request->perpage ?? Constant::ORDER_BY;

        $room = $this->room->with('categories', 'banner')
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            })
            ->when($status, function ($query) use ($status) {
                $query->whereIn('status', $status);
            })
            ->when($type, function ($query) use ($type) {
                $query->whereIn('type', $type);
            })
            ->when($type_room, function ($query) use ($type_room) {
                $query->whereIn('type_room', $type_room);
            })
            ->paginate($perPage);

        return $room;
    }

    public function createRoom(array $data)
    {
        return $this->room->create($data);
    }

    public function updateRoom(array $data, $id)
    {
        $room = $this->room->where('id', $id)->first();
        $room->update($data);
        return $room;
    }

    public function deleteMultiple(array $ids): void
    {
        $rooms = $this->room->whereIn('id', $ids)->with('banner')->get();

        foreach ($rooms as $room) {
            foreach ($room->banner as $image) {
                $this->fileService->deleteImage($image['image_data']);
                $this->fileService->deleteData($image['id']);
            }
            $this->fileService->deleteImage($room->logo);
            $room->delete();
        }

    }

    public function deleteLogo($logo_delete, $id)
    {
        $room = $this->room->where('id', $id)->first();
        $this->fileService->deleteImage($room->logo);
        if(json_decode($logo_delete)){
            $this->room->where('id', $id)->update(['logo'=>'']);
        }
    }

}
