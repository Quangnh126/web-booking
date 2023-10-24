<?php

namespace App\Services\FileUploadServices;

use Carbon\Carbon;
use Illuminate\Http\File;
use Symfony\Component\Mime\MimeTypes;
use Illuminate\Support\Facades\Storage;

class FileUploadService
{
    /**
     * @var mixed
     */
    protected $disk;

    /**
     * @var MimeTypes
     */
    protected $mime_type;

    /**
     * @author QuangNh
     */
    public function __construct()
    {
        config()->set('filesystems.disks.local.root', config('media.driver.local.root'));

        $this->disk = Storage::disk(config('filesystems.default'));
        $this->mime_type = new MimeTypes();
    }

    /**
     * Sanitize the folder name
     * @param string $folder
     * @return string
     * @author QuangNh
     */
    protected function cleanFolder(string $folder): string
    {
        return DIRECTORY_SEPARATOR . trim(str_replace('..', '', $folder), DIRECTORY_SEPARATOR);
    }

    /**
     * Return an array of file details for a file
     *
     * @param string $path
     * @return array
     * @author QuangNh
     */
    public function fileDetails(string $path): array
    {
        return [
            'filename' => basename($path),
            'url' => $this->uploadPath($path),
            'mime_type' => $this->fileMimeType($path),
            'size' => $this->fileSize($path),
            'modified' => $this->fileModified($path),
        ];
    }

    /**
     * Return the full web path to a file
     *
     * @param string $path
     * @return string
     * @author QuangNh
     */
    public function uploadPath(string $path): string
    {
        return rtrim(config('media.driver.' . config('filesystems.default') . '.path'), '/') . '/' . ltrim($path, '/');
    }

    /**
     * Return the mime type
     *
     * @param string $path
     * @return mixed|null|string
     * @author QuangNh
     */
    public function fileMimeType(string $path): mixed
    {
        return $this->mime_type->getMimeType(File::extension($this->uploadPath($path)));
    }

    /**
     * Return the file size
     *
     * @param $path
     * @return int
     * @author QuangNh
     */
    public function fileSize(string $path): int
    {
        return $this->disk->size($path);
    }

    /**
     * Return the last modified time
     *
     * @param string $path
     * @return string
     * @author QuangNh
     */
    public function fileModified(string $path): string
    {
        return Carbon::createFromTimestamp($this->disk->lastModified($path));
    }

    /**
     * Create a new directory
     *
     * @param string $folder
     * @return bool|string
     * @author QuangNh
     */
    public function createDirectory(string $folder): bool|string
    {
        $folder = $this->cleanFolder($folder);

        if ($this->disk->exists($folder)) {
            return trans('media::media.folder_exists', ['folder' => $folder]);
        }

        return $this->disk->makeDirectory($folder);
    }

    /**
     * Delete a directory
     *
     * @param string $folder
     * @return bool|string
     * @author QuangNh
     */
    public function deleteDirectory(string $folder): bool|string
    {
        $folder = $this->cleanFolder($folder);

        $filesFolders = array_merge(
            $this->disk->directories($folder),
            $this->disk->files($folder)
        );
        if (!empty($filesFolders)) {
            return trans('media::media.directory_must_empty');
        }

        return $this->disk->deleteDirectory($folder);
    }

//    /**
//     * Delete a file
//     *
//     * @param $path
//     * @return bool
//     * @author QuangNh
//     */
//    public function deleteFile($path)
//    {
//        $path = $this->cleanFolder($path);
//
//        if (!$this->disk->exists($path)) {
//            info(trans('media::media.file_not_exists'));
//        }
//
//        if ($this->is_image($this->fileMimeType($path))) {
//            $filename = pathinfo($path, PATHINFO_FILENAME);
//            $files = [$path];
//            foreach (config('media.sizes') as $size) {
//                $files[] = str_replace($filename, $filename . '-' . $size, $path);
//            }
//
//            return $this->disk->delete($files);
//        }
//        return $this->disk->delete([$path]);
//    }

    /**
     * Save a file
     *
     * @param string $path
     * @param string $content
     * @return bool|string
     * @author QuangNh
     */
    public function saveFile(string $path,string $content): bool|string
    {
        $path = $this->cleanFolder($path);

        return $this->disk->put($path, $content);
    }
}
