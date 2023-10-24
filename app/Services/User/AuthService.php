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
    public function __construct(
        PasswordReset $passwordReset,
        User $user,
        Messages $messages,
        Merchandises $merchandises
    )
    {
        $this->passwordReset = $passwordReset;
        $this->user = $user;
        $this->messages = $messages;
        $this->merchandises = $merchandises;
    }
    /**
     * check code record
     * @param Request $request
     * @param string $type
     * @return object $code
     * @author QuangNh
     */
    public function getCode(Request $request, string $type)
    {
        return $this->passwordReset->where('email', $request->email)
            ->where('code', $request->code)
            ->where('created_at', '>=', now()->subMinutes(2))
            ->where('type', $type)
            ->first();
    }

    /**
     * check code record
     * @param Request $request
     * @param string $type
     * @author QuangNh
     */
    public function deleteCode(Request $request, string $type)
    {
        $deleteCode = $this->passwordReset->where('email', $request->email)
            ->where('code', $request->code)
            ->where('type', $type)
            ->delete();

        Log::debug('Xóa mã code cũ: ' . $deleteCode);
    }


    /**
     * save code record
     * @param string $email
     * @param string $code
     * @param string $type
     * @author QuangNh
     */
    public function saveCode(string $email, string $code, string $type)
    {
        return $this->passwordReset->updateOrInsert(
            [
                'email' => $email,
                'type' => $type
            ],
            [
                'code' => $code,
                'created_at' => Carbon::now(),
            ]);
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
     * active account
     * @param object $user
     * @param Request $request
     * @author QuangNh
     */
    public function chatList(object $user, Request $request)
    {
        $perPage = Constant::ORDER_BY;
        $currentPage = $request->page ?? 1;
        $offset = ($currentPage - 1) * $perPage;

        $displayName = $request->keyword;

        $result = $this->messages->with('user')->where('receiver_id', $user->id)->groupBy('sender_id')
            ->offset($offset)->limit($perPage)->get();

        if ($displayName) {
            $result = $result->where('users.display_name', $displayName);
        }

        return $result;
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

    /**
     * get merchandise by user Logged
     * @param int $userId
     * @return  array $merchandises
     * @author QuangNh
     */
    public function merchandiseByUserLogged(int $userId): array
    {
        $merchandises['given'] = $this->merchandises->ofGiver($userId)
            ->ofStatus(Merchandises::$has_gift)
            ->with('merchandiseImage')
            ->select('*')
            ->orderBy('id', 'DESC')
            ->paginate(Constant::ORDER_BY);

        $merchandises['noGiven'] = $this->merchandises->ofGiver($userId)
            ->ofStatus(Merchandises::$not_gift)
            ->with('merchandiseImage')
            ->select('*')
            ->orderBy('id', 'DESC')
            ->paginate(Constant::ORDER_BY);

        $merchandises['received'] = $this->transaction->ofReceiver($userId)
            ->with(['merchandises','merchandiseImage'])
            ->select('id', 'merchandise_id', 'amount')
            ->orderBy('id', 'DESC')
            ->paginate(Constant::ORDER_BY);

        return $merchandises;
    }

    /**
     * get merchandise by userID
     * @return  array $merchandises
     * @author QuangNh
     */
    public function merchandiseByUserId($userId): array
    {
        $merchandises['given'] = $this->merchandises->ofGiver($userId)
            ->with('merchandiseImage')
            ->ofStatus(Merchandises::$has_gift)
            ->select('*')
            ->orderBy('id', 'DESC')
            ->paginate(Constant::ORDER_BY);

        $merchandises['noGiven'] = $this->merchandises->ofGiver($userId)
            ->with('merchandiseImage')
            ->ofStatus(Merchandises::$not_gift)
            ->select('*')
            ->orderBy('id', 'DESC')
            ->paginate(Constant::ORDER_BY);

        $merchandises['received'] = $this->transaction->ofReceiver($userId)
            ->with(['merchandises','merchandiseImage'])
            ->select('id', 'merchandise_id')
            ->orderBy('id', 'DESC')
            ->paginate(Constant::ORDER_BY);

        return $merchandises;
    }

    /**
     * insert report user
     * @param  array $req
     * @return  object $req
     * @author QuangNh
     */
    public function reportUser(array $req): object
    {
        return $this->reported->create($req);
    }

    /**
     * insert block user
     * @param  array $req
     * @return  object $req
     * @author QuangNh
     */
    public function blockUser(array $req): object
    {
        return $this->blocked->updateOrCreate($req);
    }

    /**
     * insert block user
     * @param  array $req
     * @return  integer $req
     * @author QuangNh
     */
    public function unBlockUser(array $req): int
    {
        return $this->blocked->where('giver_id', $req['giver_id'])
            ->where('receiver_id', $req['receiver_id'])
            ->delete();
    }
}
