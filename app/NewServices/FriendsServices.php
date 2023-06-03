<?php

namespace App\NewServices;

use App\Enums\FriendsStatusEnum;
use App\Models\Friends;
use App\Models\Users;
use App\NewLogics\SysMessageLogics;
use Exception;
use LaravelCommon\App\Exceptions\Err;

class FriendsServices
{
    /**
     * @param Users $me
     * @param Users $friend
     * @return void
     * @throws Exception
     */
    public static function Follow(Users $me, Users $friend): void
    {
        self::canNotFollowSelf($me, $friend);
        $status = self::getFriendFollowMeStatus($friend, $me);
        $myStatus = match ($status) {
            FriendsStatusEnum::Yes->name, FriendsStatusEnum::Both->name => FriendsStatusEnum::Both->name,
            default => FriendsStatusEnum::Yes->name,
        };

        // 我关注朋友
        $flag = Friends::updateOrCreate([
            'from_users_id' => $me->id,
            'to_users_id' => $friend->id,
        ], [
            'status' => $myStatus
        ]);
        if ($flag == 2) {
            SysMessageLogics::AddFriend($friend, $me);
        }

        // 朋友是否需要更新
        if ($status == FriendsStatusEnum::Yes->name) {
            Friends::updateOrCreate([
                'from_users_id' => $friend->id,
                'to_users_id' => $me->id,
            ], [
                'status' => FriendsStatusEnum::Both->name
            ]);
        }
    }

    /**
     * @param Users $me
     * @param Users $friend
     * @return void
     * @throws Err
     */
    public static function UnFollow(Users $me, Users $friend): void
    {
        self::canNotFollowSelf($me, $friend);

        // 我取消关注朋友
        Friends::updateOrCreate([
            'from_users_id' => $me->id,
            'to_users_id' => $friend->id,
        ], [
            'status' => FriendsStatusEnum::No->name
        ]);

        // 更新朋友关注状态
        $status = self::getFriendFollowMeStatus($friend, $me);
        if ($status == FriendsStatusEnum::Both->name) {
            Friends::updateOrCreate([
                'from_users_id' => $friend->id,
                'to_users_id' => $me->id,
            ], [
                'status' => FriendsStatusEnum::Yes->name
            ]);
        }
    }

    /**
     * @param Users $fromUser
     * @param Users $toUser
     * @return void
     * @throws Err
     */
    public static function CheckIsBoth(Users $fromUser, Users $toUser): void
    {
        $follow = Friends::where('from_users_id', $fromUser->id)
            ->where('to_users_id', $toUser->id)
            ->where('status', FriendsStatusEnum::Both->name)
            ->first();
        if (!$follow)
            Err::Throw(__("The friend haven't follow you"));
    }

    /**
     * @param Users $fromUser
     * @param Users $toUser
     * @return void
     * @throws Err
     */
    public static function CheckIsFollowMe(Users $fromUser, Users $toUser): void
    {
        $follow = Friends::where('from_users_id', $fromUser->id)
            ->where('to_users_id', $toUser->id)
            ->whereIn('status', [FriendsStatusEnum::Both->name, FriendsStatusEnum::Yes->name])
            ->first();
        if (!$follow)
            Err::Throw(__("The friend haven't follow you"));
    }

    /**
     * @ok
     * @param $user
     * @param $parent
     * @return void
     * @throws Exception
     */
    public static function BothFollow($user, $parent): void
    {
        $flag = Friends::updateOrCreate(
            [
                'from_users_id' => $user->id,
                'to_users_id' => $parent->id
            ], [
                'status' => FriendsStatusEnum::Both->name
            ]
        );
        if ($flag == 2)
            SysMessageLogics::AddFriend($parent, $user);

        $flag = Friends::updateOrCreate(
            [
                'from_users_id' => $parent->id,
                'to_users_id' => $user->id
            ], [
                'status' => FriendsStatusEnum::Both->name
            ]
        );
        if ($flag == 2)
            SysMessageLogics::AddFriend($user, $parent);

    }

    /**
     * @param Users $friend
     * @param Users $me
     * @return ?string
     */
    private static function getFriendFollowMeStatus(Users $friend, Users $me): ?string
    {
        $follow = Friends::where('from_users_id', $friend->id)
            ->where('to_users_id', $me->id)
            ->first();

        if (!$follow)
            return null;
        else
            return $follow->status;
    }

    /**
     * @param Users $me
     * @param int $friendId
     * @return string|null
     */
    public static function GetFollowStatus(Users $me, int $friendId): ?string
    {
        if ($me->id == $friendId)
            return null;

        $follow = Friends::where('from_users_id', $me->id)
            ->where('to_users_id', $friendId)
            ->first();

        if (!$follow)
            return FriendsStatusEnum::No->name;
        return $follow->status;
    }

    /**
     * @param Users $me
     * @param Users $friend
     * @return void
     * @throws Err
     */
    private static function canNotFollowSelf(Users $me, Users $friend): void
    {
        if ($me->id == $friend->id)
            Err::Throw(__("Can not follow/unfollow self"));
    }
}
