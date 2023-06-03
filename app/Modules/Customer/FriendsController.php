<?php

namespace App\Modules\Customer;

use App\Enums\AssetsPendingStatusEnum;
use App\Enums\FriendsStatusEnum;
use App\Helpers\TelegramBot\TelegramBotApi;
use App\Models\Friends;
use App\Modules\CustomerBaseController;
use App\NewLogics\SysMessageLogics;
use App\NewServices\AssetsServices;
use App\NewServices\FriendsServices;
use App\NewServices\UsersServices;
use App\NewServices\VipsServices;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\ArrayShape;
use LaravelCommon\App\Exceptions\Err;

/**
 * @intro 朋友相关
 */
class FriendsController extends CustomerBaseController
{
    /**
     * @intro 关注
     * @param Request $request
     * @return void
     * @throws Err
     * @throws Exception
     */
    public function follow(Request $request): void
    {
        $params = $request->validate([
            'users_id' => 'required|integer' # 对方用户id,
        ]);
        $me = $this->getUser();
        $friend = UsersServices::GetById($params['users_id']);
        FriendsServices::Follow($me, $friend);
    }

    /**
     * @intro 取消关注
     * @param Request $request
     * @return void
     * @throws Err
     */
    public function unFollow(Request $request): void
    {
        $params = $request->validate([
            'users_id' => 'required|integer' # 对方用户id,
        ]);
        $me = $this->getUser();
        $friend = UsersServices::GetById($params['users_id']);
        FriendsServices::UnFollow($me, $friend);
    }

    /**
     * @intro 我的朋友列表
     * @return mixed
     * @throws Err
     */
    public function MyFriends(): mixed
    {
        $user = $this->getUser();
        return Friends::where('from_users_id', $user->id)
            ->with(['to_user' => function ($q) {
                $q->selectRaw('id,vips_id,nickname,avatar,address,today_had_help_count')
                    ->with('vip:id,max_help_withdraw_count');
            }])
            ->where('status', FriendsStatusEnum::Both->name)
            ->paginate($this->perPage());
    }

    /**
     * @intro 查看朋友头像昵称
     * @param Request $request
     * @return array
     * @throws Err
     */
    #[ArrayShape(['friend' => "array"])]
    public function showFriend(Request $request): array
    {
        $params = $request->validate([
            'users_id' => 'required|integer' # 对方用户id,
        ]);
        $me = $this->getUser();
        $friend = UsersServices::GetById($params['users_id']);
        FriendsServices::CheckIsFollowMe($friend, $me);
        return [
            'friend' => $friend->only('id', 'nickname', 'avatar', 'address'),
        ];
    }

    /**
     * @intro 发送邀请给朋友
     * @param Request $request
     * @return void
     */
    public function sendInvite(Request $request): void
    {
        $params = $request->validate([
            'assets_id' => 'required|integer', # 提现单id,
            'to_users_id' => 'required|integer' # 对方用户id,
        ]);
        DB::transaction(function () use ($params) {
            $fromUser = $this->getUser();
            $toUser = UsersServices::GetById($params['to_users_id']);
            $toVip = VipsServices::GetById($toUser->vips_id);

            // 是否双向关注
            FriendsServices::CheckIsBoth($fromUser, $toUser);

            // 提现单状态是否存在、是否等待状态、是否是30美元
            $asset = AssetsServices::GetById($params['assets_id'], lock: true);
            if ($asset->pending_status != AssetsPendingStatusEnum::APPROVE->name)
                Err::Throw(__("The withdrawal was approved by other friends."));
            if ($asset->pending_fee + $asset->balance != 30)
                Err::Throw(__("The withdrawal amount is not $30"));

            // 是否已经发送过给好友
            $ids = $asset->pending_withdrawal_approve_users ? json_decode($asset->pending_withdrawal_approve_users) : [];
            if (in_array($params['to_users_id'], $ids)) {
                Err::Throw(__("The withdrawal has been sent to the friend."));
            }

            // 好友Vip等级对应的辅助次数是否超过
            if ($toUser->today_had_help_count >= $toVip->max_help_withdraw_count) {
                Err::Throw(__("Your friend's daily assistance has reached the upper limit"));
            }

            // 成功
            $ids[] = $params['to_users_id'];
            $ids = array_unique($ids); // 去重
            $asset->pending_status = AssetsPendingStatusEnum::APPROVE->name;
            $asset->pending_withdrawal_approve_users = json_encode($ids);
            $asset->save();

            SysMessageLogics::WithdrawalInvite($fromUser, $toUser, $asset);
        });
    }

    /**
     * @param Request $request
     * @return array
     * @throws Err
     */
    #[ArrayShape(['pending' => "array", 'user' => "array"])]
    public function show(Request $request): array
    {
        $params = $request->validate([
            'assets_id' => 'required|integer' # 提现单id
        ]);
        $toUser = $this->getUser();
        $asset = AssetsServices::GetById($params['assets_id']);
        $fromUser = UsersServices::GetById($asset->users_id);

        $ids = $asset->pending_withdrawal_approve_users ? json_decode($asset->pending_withdrawal_approve_users) : [];
        if (!in_array($toUser->id, $ids)) {
            Err::Throw(__("This record is not yours"));
        }

        FriendsServices::CheckIsBoth($fromUser, $toUser);

        return [
            'pending' => $asset->only('id', 'balance', 'coin_symbol'),
            'user' => $fromUser->only('id', 'nickname', 'avatar', 'address')
        ];
    }

    /**
     * @param Request $request
     * @return void
     */
    public function approve(Request $request): void
    {
        $params = $request->validate([
            'assets_id' => 'required|integer' # 提现单id
        ]);
        DB::transaction(function () use ($params) {
            // 提现单状态是否存在、是否等待状态、是否是30美元
            $asset = AssetsServices::GetById($params['assets_id'], lock: true);
            if ($asset->pending_status != AssetsPendingStatusEnum::APPROVE->name)
                Err::Throw(__("The withdrawal was approved by other friends."));
            if ($asset->pending_fee + $asset->balance != 30)
                Err::Throw(__("The withdrawal amount is not $30"));

            $toUser = $this->getUser();
            $fromUser = UsersServices::GetById($asset->users_id);
            $toVip = VipsServices::GetById($toUser->vips_id);

            // 是否双向关注
            FriendsServices::CheckIsBoth($fromUser, $toUser);

            // 是否在被邀请名单中
            $ids = $asset->pending_withdrawal_approve_users ? json_decode($asset->pending_withdrawal_approve_users) : [];
            if (!in_array($toUser->id, $ids))
                Err::Throw(__("You are not on the list to invite help"));

            // Vip等级对应的辅助次数是否超过
            if ($toUser->today_had_help_count >= $toVip->max_help_withdraw_count) {
                Err::Throw(__("Your daily assistance has reached the upper limit"));
            }

            // 成功
            $asset->pending_status = AssetsPendingStatusEnum::WAITING->name;
            $asset->pending_withdrawal_approve_users_id = $toUser->id;
            $asset->save();

            $toUser->today_had_help_count++;
            $toUser->save();

            SysMessageLogics::FriendHelpWithdrawal($fromUser, $asset, $toUser);
            TelegramBotApi::SendText("提现好有协助完成待审批\n[$fromUser->nickname]\n$asset->balance $asset->symbol");
        });
    }

    /**
     * @intro 发送留言
     * @param Request $request
     * @return void
     * @throws Err
     */
    public function sendMessage(Request $request): void
    {
        $params = $request->validate([
            'users_id' => 'required|integer', # 对方用户id,
            'content' => 'required|string' # 内容
        ]);
        $fromUser = $this->getUser();
        if (!$fromUser->can_say)
            Err::Throw(__('You are not allowed to send messages'));

        $toUser = UsersServices::GetById($params['users_id']);
        FriendsServices::CheckIsBoth($fromUser, $toUser);
        SysMessageLogics::SendFriendMessage($fromUser, $toUser, $params['content']);
    }
}
