<?php


namespace App\Services\Web;


use App\Enums\Constant;
use App\Models\User;
use App\Services\FileUploadServices\FileService;

class UserService
{
    private $user;
    private $fileService;

    public function __construct(
        User $user,
        FileService $fileService
    ) {
        $this->user = $user;
        $this->fileService = $fileService;
    }

    /**
     * update user
     */
    public function updateUser(array $req, int $id)
    {
        $staff = $this->user->where('id', $id)->first();
        if (json_decode($req['image_delete'])) {
            $staff->update(['avatar' => '']);
        }

        $staff->update($req);
        return $staff;
    }
}
