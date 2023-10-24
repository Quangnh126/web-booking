<?php


namespace App\Http\Traits;


trait AuthTrait
{
    public function getCurrentLoggedIn(): ?\Illuminate\Contracts\Auth\Authenticatable
    {
        return auth('sanctum')->user();
    }
}
