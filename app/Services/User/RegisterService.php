<?php


namespace App\Services\User;


use App\Models\User;
use App\Enums\Constant;
use App\Services\FileUploadServices\FileService;
use PhpOffice\PhpSpreadsheet\Calculation\Web\Service;

class RegisterService extends Service
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

    public function checkIdNotExist(array $ids_delete): string
    {

        $list_users = $this->user->whereIn('role_id', [User::$staff, User::$admin])->pluck('id')->toArray();
        $ids_not_exist = implode(", ", array_diff($ids_delete, $list_users));

        return $ids_not_exist;
    }

    /**
     * register a user
     */
    public function register(array $req)
    {
        return $this->user->create($req);
    }
}
