<?php


namespace App\Services\Admin;


use App\Models\User;
use App\Enums\Constant;
use App\Services\FileUploadServices\FileService;
use PhpOffice\PhpSpreadsheet\Calculation\Web\Service;

class CustomerService extends Service 
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

    public function listCustomer($request)
    {
        $search = $request->search;
        $role_id = $request->role_id;
        $perPage = $request->perpage ?? Constant::ORDER_BY;
        $role_staff = [User::$admin, User::$staff , User::$user];

        $query = $this->user->select('users.*')->where('role_id',2)->paginate($perPage);;

        return $query;
    }

    public function checkIdNotExist(array $ids_delete): string
    {

        $list_users = $this->user->where('role_id', User::$user)->pluck('id')->toArray();
        $ids_not_exist = implode(", ", array_diff($ids_delete, $list_users));

        return $ids_not_exist;
    }

    /**
     * @param array $ids_delete
     */
    public function deleteMultipleCustomer(array $ids_delete): void
    {
        $staff = $this->user->whereIn('id', $ids_delete)->get();
        foreach ($staff as $item) {
            $this->fileService->deleteImage($item->avatar);
            $item->delete();
        }
    }

    /**
     * create customer
     */
    public function createCustomer(array $req)
    {
        return $this->user->create($req);
    }

    /**
     * updateStatus Customer
     */
    public function updateCustomer( $req, int $id)
    {
        $customer = $this->user->findOrFail($id);
        $customer->status = $req;       
        $customer->update();
        return $customer;
    }
}