<?php

namespace App\Modules\Customer;

use App\Enums\CacheTagsEnum;
use App\Models\Users;
use App\Modules\CustomerBaseController;
use App\NewServices\AssetsServices;
use App\NewServices\FriendsServices;
use App\NewServices\NewbieCardServices;
use App\NewServices\PledgesServices;
use App\NewServices\UsersServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use LaravelCommon\App\Exceptions\Err;

class UsersController extends CustomerBaseController
{
    /**
     * @intro 获取用户小窗口信息
     * @param Request $request
     * @return mixed
     * @throws Err
     */
    public function show(Request $request): mixed
    {
        $params = $request->validate([
            'id' => 'required|integer' # 用户id
        ]);
        $me = $this->getUser();
        $user = Users::selectRaw('id,nickname,avatar,address,vips_id,total_income,total_loyalty_value')->idp($params)->toArray();
        $user['online_status'] = Cache::tags([CacheTagsEnum::OnlineStatus->name])->get($user['id'], false);
        $user['follow_status'] = FriendsServices::GetFollowStatus($me, $user['id']);
        return $user;
    }

    /**
     * @intro 用户领取新手卡
     * @return array
     * @throws Err
     */
    public function getNewbieCard(): array
    {
        $user = $this->getUser();
        NewbieCardServices::UserGetNewbieCard($user);
        return [__('You received the new bie card successfully')];
    }

    /**
     * @intro for user who has not received the first withdrawal free
     * if users first_withdrawal_free is false, then set it to true
     * then return success
     * @return array
     * @throws Err
     */
    public function getFirstWithdrawalFree(): array
    {
        $me = $this->getUser();

        if (AssetsServices::getAllUserStaking($me) < 100) {
            throw new Err(__('Dont try to cheat me!'));
        }

        if ($me->vips_id != 1) {
            throw new Err(__('You are not a newbie'));
        }

        $cardStatus = UsersServices::getFirstWithdrawalStatus($me);

        if ($cardStatus) {
            throw new Err(__('You have already received the first withdrawal free'));
        }

        $me->first_withdrawal_free = true;
        $me->save();

        return [__('You received the first withdrawal free successfully')];
    }
}
