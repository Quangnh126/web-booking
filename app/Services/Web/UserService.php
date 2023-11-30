<?php


namespace App\Services\Web;


use App\Enums\Constant;
use App\Models\PasswordCode;
use App\Models\User;
use App\Services\FileUploadServices\FileService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserService
{
    private $user;
    private $fileService;
    private $passwordCode;

    public function __construct(
        User $user,
        FileService $fileService,
        PasswordCode $passwordCode
    ) {
        $this->user = $user;
        $this->fileService = $fileService;
        $this->passwordCode = $passwordCode;
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

    /**
     * check code record
     * @author Quangnh
     */
    public function getCode(Request $request): object|null
    {
        return $this->passwordCode->ofEmail($request->email)
            ->ofCode($request->code)
            ->where('updated_at', '>=', now()->subMinutes(config('app.time_otp')))
            ->first();
    }

    /**
     * check code record
     * @author Quangnh
     */
    public function getCodeToReset(Request $request): object|null
    {
        return $this->passwordCode->ofEmail($request->email)
            ->ofCode($request->code)
            ->first();
    }

    /**
     * check code record
     * @author Quangnh
     */
    public function deleteCode(Request $request): void
    {
        $this->passwordCode->ofEmail($request->email)
            ->ofCode($request->code)
            ->delete();
    }

    /**
     * save code record
     * @author Quangnh
     */
    public function saveCode(string $email, string $code)
    {
        return $this->passwordCode->updateOrCreate(
            [
                'email' => $email,
            ],
            [
                'code' => $code,
                'created_at' => Carbon::now(),
            ]);
    }

    /**
     * set new password
     * @author Quangnh
     */
    public function newPassword(string $email, string $password): void
    {
        $this->user->ofEmail($email)->update([
            'password' => Hash::make($password)
        ]);
    }

    /**
     * active account
     * @author Quangnh
     */
    public function activeAccount(object $user): void
    {
        $user->update(
            [
                'verify' => User::$verify
            ]);
    }

}
