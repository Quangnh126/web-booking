<?php


namespace App\Services\Admin;


use App\Enums\Constant;
use App\Models\User;

class StaffV2Service
{
    private $user;

    public function __construct(
        User $user
    )
    {
        $this->user = $user;
    }

    /**
     * list all staff and admin
     * @return mixed
     */
    public function listStaff($request)
    {
        $search = $request->search;
        $role_id = $request->role_id;
        $perPage = $request->perpage ?? Constant::ORDER_BY;
        $role_staff = [User::$admin, User::$staff];

        $staff = $this->user->ofStaff($role_staff)
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('email', 'LIKE', '%' . $search . '%')
                        ->orWhere('display_name', 'LIKE', '%' . $search . '%');
                });
            })
            ->when($role_id, function ($query, $role_id) {
                $query->whereIn('role_id', $role_id);
            })
            ->paginate($perPage);

        return $staff;
    }

    /**
     * create staff
     */
    public function createStaff(array $req)
    {
        return $this->user->create($req);
    }

    /**
     * update staff
     */
    public function updateStaff(array $req, int $id)
    {
        $staff = $this->user->where('id', $id)->first();
        if(json_decode($req['image_delete'])){
            $staff->update(['avatar'=>'']);
        }

        $staff->update($req);
        return $staff;
    }

    /**
     * delete staff
     */
    public function deleteStaff(int $id): void
    {
        $staff = $this->user->where('id', $id)->first()->delete();
    }

    /**
     * @param array $ids_delete
     * @return string
     */
    public function checkIdNotExist(array $ids_delete): string
    {

        $list_users = $this->user->whereIn('role_id', [User::$staff, User::$admin])->pluck('id')->toArray();
        $ids_not_exist = implode(", ", array_diff($ids_delete, $list_users));

        return $ids_not_exist;
    }

    /**
     * @param array $ids_delete
     */
    public function deleteMultipleStaff(array $ids_delete): void
    {
        $this->user->whereIn('id', $ids_delete)->delete();
    }

}
