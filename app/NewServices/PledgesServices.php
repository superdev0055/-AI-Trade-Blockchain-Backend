<?php

namespace App\NewServices;

use App\Enums\PledgesStatusEnum;
use App\Models\Pledges;
use App\Models\Users;
use Exception;
use LaravelCommon\App\Exceptions\Err;
use LaravelCommon\App\Helpers\CommonHelper;

class PledgesServices
{
    /**
     * @ok
     * @param Users $user
     * @return Pledges|null
     */
    public static function GetByUser(Users $user): ?Pledges
    {
        return Pledges::where('users_id', $user->id)
            ->where('status', PledgesStatusEnum::OnGoing->name)
            ->orderByDesc('id')
            ->first();
    }

    /**
     * @param Users $user
     * @param int $lever
     * @return void
     * @throws Err
     */
    public static function UpdateLeverage(Users $user, int $lever): void
    {
        # 杠杆范围
        $arr = [1, 5, 10, 20, 40, 60, 80, 100, 120, 125];
        if (!in_array($lever, $arr))
            Err::Throw(__("Dont Try To Test"));

        $vip = VipsServices::GetByUser($user);

        $pledge = PledgesServices::GetByUser($user);
        if (!$pledge)
            Err::Throw(__("You haven't start a ai trade"));

        $isNewbieCardValid = NewbieCardServices::IsNewbieCardValid($user);
        $isNewbieCardValid = ($lever > 60) ? false : $isNewbieCardValid;

        # VIP权限
        if ($lever > $vip->leveraged_investment && !$pledge->is_trail && !$isNewbieCardValid)
            Err::Throw(__("Please upgrade your vip level"));

        # 试用阶段
        if ($pledge->is_trail) {
            $config = ConfigsServices::Get('trail');
            $leverageSetting = $config['leverage'];
            if ($lever > $leverageSetting && !$isNewbieCardValid)
                Err::Throw(__("The max lever is") . $leverageSetting);
        }

        $user->leverage = $lever;
        $user->save();
    }

    /**
     * @param Users $user
     * @param int $days
     * @return void
     * @throws Err
     * @throws Exception
     */
    public static function UpdateDuration(Users $user, int $days): void
    {
        $pledge = PledgesServices::GetByUser($user);

        if ($pledge && $pledge->is_trail)
            Err::Throw(__("Trail can not change duration"));

        if ($days == 3 && !($pledge && $pledge->is_trail))
            Err::Throw(__("Only trail can select 3 days"));

        $arr = [7, 15, 30, 60, 90, 180, 360];
        if (!in_array($days, $arr))
            Err::Throw(__("Dont Try To Test"));

        if ($pledge && $days < $user->duration)
            Err::Throw(__("You are running AI Trade now, not allow to decrease duration"));

        $vip = VipsServices::GetByUser($user);
        if ($days > $vip->max_staking_term)
            Err::Throw(__("You can't modify duration, please upgrade your vip level"));

        // 更新 user
        $user->duration = $days;
        $user->save();

        //  更新 staking
        $day = now()->addDays($days)->startOfDay()->addHours(18)->toDateTimeString();
        $staking = AssetsServices::getOrCreateStakingAsset($user);
        if ($staking->balance > 0) {
            $staking->staking_ended_at = $day;
            $staking->save();
        }

        //  更新 pledge
        $pledge = PledgesServices::GetByUser($user);
        if ($pledge) {
            $pledge->ended_at = $day;
            $pledge->save();
        }
    }

    /**
     * @param int $pledges_id
     * @return Pledges
     */
    public static function GetById(int $pledges_id): Pledges
    {
        return Pledges::findOrFail($pledges_id);
    }
}
