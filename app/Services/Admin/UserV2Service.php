<?php


namespace App\Services\Admin;


use App\Models\User;

class UserV2Service
{

    private $user;

    public function __construct(
        User $user
    )
    {
        $this->user = $user;
    }

}
