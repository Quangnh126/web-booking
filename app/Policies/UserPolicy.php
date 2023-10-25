<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return in_array($user->role_id, [User::$admin, User::$staff]);
    }

    public function viewStaff(User $user): \Illuminate\Auth\Access\Response|bool
    {
        return $user->role_id == User::$admin;
        return in_array($user->role_id, [User::$admin, User::$staff]);
    }

    /**
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function superAdmin(User $user)
    {
        return in_array($user->role_id, [User::$admin]);
    }
}
