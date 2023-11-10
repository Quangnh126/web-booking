<?php

namespace App\Services\User;

use App\Enums\Constant;
use App\Models\Blocked;
use App\Models\Merchandises;
use App\Models\Messages;
use App\Models\PasswordReset;
use App\Models\Reported;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AuthService
{
    private $user;

    public function __construct(
        User $user
    )
    {
        $this->user = $user;
    }

    /**
     * set new password
     * @param string $email
     * @param string $password
     * @author QuangNh
     */
    public function newPassword(string $email, string $password)
    {
        $newPassword = $this->user->where('email', $email)->update([
            'password' => Hash::make($password)
        ]);

        Log::debug('Mật khẩu mới: ' . $newPassword);
    }

    public function changePassword($id, string $password)
    {
        $newPassword = $this->user->where('id', $id)->update([
            'password' => Hash::make($password)
        ]);

        Log::debug('Mật khẩu mới: ' . $newPassword);
        return $newPassword;
    }

    /**
     * edit profile
     * @param object $user
     * @param array $req
     * @author QuangNh
     */
    public function editProfile(object $user, array $req)
    {
        $user->update($req);
    }

    /**
     * active account
     * @param object $user
     * @author QuangNh
     */
    public function activeAccount(object $user)
    {
        $activeAccount = $user->update(['verify' => User::$verify]);

        Log::debug('Kích hoạt tài khoản: ' . $activeAccount);
    }

    /**
     * del account
     * @param object $user
     * @author QuangNh
     */
    public function deleteAccount(object $user)
    {
        $this->deleteAvatar($user->avatar);

        $deleteAccount = $user->delete();

        Log::debug('Xóa tài khoản: ' . $deleteAccount);

        return response()->json([
            'status' => Constant::SUCCESS_CODE,
            'message' => trans('messages.success.users.delete'),
            'data' => $user
        ], Constant::SUCCESS_CODE);
    }

    /**
     * del avatar
     * @param string $path
     * @author QuangNh
     */
    public function deleteAvatar(string $path)
    {
        $deleteAvatar = Storage::delete('public/' . $path);

        Log::debug('Xóa ảnh đại diện: ' . $deleteAvatar);
    }

    public function register(array $req)
    {
        return $this->user->create($req);
    }
}
