<?php

namespace App\Services\FileUploadServices;

use App\Models\MerchandiseImages;
use App\Models\MessageImages;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileService
{
    /**
     * store path
     * @param object $req
     * @param string $type
     * @return false|string $path
     * @author QuangNh
     */
    public function getFilePath(object $req, string $type): string|false
    {
        $isImage = $this->checkValidate($req);
        if ($isImage) {
            // lấy filename
            $imageName = Str::random(16) . '.' . $req->getClientOriginalExtension();
            // lấy đường dẫn
            $filePath = $type;
            // size ảnh
            $imgSize = [800,600];

            $thumbnailService = new FileProcessService();

            $thumbnailService
                ->setImage($req)
                ->setSize($imgSize[0], $imgSize[1])
                ->setDestinationPath($type)
                ->setFileName($imageName)
                ->save();

            return $type . '/' . $imageName;
        }

        return false;
    }

    /**
     * validate image
     * @param object $req
     * @return boolean $isImage
     * @author QuangNh
     */
    public function checkValidate(object $req): bool
    {
//        $rules = array('jpeg','jpg','png','gif', 'svg');
//        $typeOfFile = $req->extension();

        $sizeOfFile = number_format($req->getSize() / 10485760,2);

        return $sizeOfFile<10;
//        return (in_array($typeOfFile, $rules) && $sizeOfFile<10);
    }

    /**
     * del img
     * @param string $type
     * @author QuangNh
     */
    public function deleteFile($deleteImageId, string $type)
    {
        switch ($type) {
            case 'merchandises':
                $model = new MerchandiseImages();
                break;
            case 'message':
                $model = new MessageImages();
                break;
            default:
                Log::debug('Thể loại ảnh xóa: ' . $type);

                return false;
        }

        foreach ($deleteImageId as $key => $delId) {
            if (is_object($delId)) {
                $delId = $delId->id;
            }
            $file = $model->where('id', $delId)->first();
            if (isset($file)) {
                Storage::delete('public/' . $file->image_data);
                $deleteFile = $file->delete();

                Log::debug('Xóa file: ' . $key . ' ' . $deleteFile);
            }
        }
    }
}
