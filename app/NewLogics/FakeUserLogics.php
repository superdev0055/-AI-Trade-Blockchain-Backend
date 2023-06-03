<?php

namespace App\NewLogics;

use App\Helpers\Aws\AwsS3Helper;
use App\Models\FakeUsers;
use App\Models\Users;
use App\NewServices\UsersServices;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use LaravelCommon\App\Exceptions\Err;

class FakeUserLogics
{
    /**
     * @param mixed $id
     * @return FakeUsers
     * @throws Err
     */
    public static function GetById(mixed $id): FakeUsers
    {
        $fakeUser = FakeUsers::find($id);
        if (!$fakeUser)
            Err::Throw(__("User does not exist"));
        return $fakeUser;
    }

    /**
     * @ok
     * @return void
     * @throws Exception
     */
    public static function CreateUser(): void
    {
        try {
            $res = Http::asJson()->post(config('web3.service.py') . '/create_users', [
                'num' => 1,
            ]);
        } catch (Exception $exception) {
            dump('创建用户失败::' . $exception->getMessage());
            throw $exception;
        }
        // download avatar
        $avatar = $res[0]['avatar'];
        $content = Http::get($avatar)->body();
        $content = 'data:image/jpg;base64,' . base64_encode($content);
        // upload avatar
        $url = AwsS3Helper::Store($content, 'avatar');
        // create user
        FakeUsers::create([
            'address' => $res[0]['address'], #
            'private_key' => $res[0]['private_key'], #
            'nickname' => $res[0]['nickname'], #
            'avatar' => $url, #
            'email' => $res[0]['email'], #
        ]);
    }

    /**
     * @param array $params
     * @return void
     * @throws Err
     */
    public static function Update(array $params): void
    {
        $fakeUser = self::GetById($params['id']);
        if (!Str::startsWith($params['avatar'], '/uploads/')) {
            $avatar = $params['avatar'];
            $params['avatar'] = AwsS3Helper::Store($avatar, 'avatar');
        }
        $fakeUser->update($params);

        if ($fakeUser->users_id) {
            $user = UsersServices::GetById($fakeUser->users_id);
            $user->avatar = $fakeUser->avatar;
            $user->nickname = $fakeUser->nickname;
            $user->email = $fakeUser->email;
            $user->save();
        }
    }

    /**
     * @ok
     * @param FakeUsers $fakeUser
     * @param Users|null $parent
     * @return void
     * @throws Err
     */
    public static function ToUser(FakeUsers $fakeUser, ?Users $parent): void
    {
        if ($fakeUser->users_id)
            Err::Throw(__("The user has already synced"));

        $user = UsersServices::GetByAddress($fakeUser->address);
        if ($user)
            Err::Throw(_("user already exists"));

        $user = UsersServices::AutoRegisterUser([
            'address' => $fakeUser->address,
            'inviteCode' => $parent?->invite_code,
            'avatar' => $fakeUser->avatar,
            'email' => $fakeUser->email,
            'nickname' => $fakeUser->nickname,
        ], true);

        $fakeUser->users_id = $user->id;
        $fakeUser->parent_address = $user->address;
        $fakeUser->save();
    }

    /**
     * @param Users $user
     * @return bool
     */
    public static function IsFakeUser(Users $user): bool
    {
        return FakeUsers::where('users_id', $user->id)->exists();
    }


}
