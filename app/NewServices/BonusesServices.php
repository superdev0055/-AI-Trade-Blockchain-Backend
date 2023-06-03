<?php

namespace App\NewServices;

use App\Enums\AssetLogsRemarkEnum;
use App\Enums\AssetsPendingStatusEnum;
use App\Enums\AssetsPendingTypeEnum;
use App\Enums\AssetsTypeEnum;
use App\Enums\UserBonusesStatusEnum;
use App\Enums\UserBonusesTypeEnum;
use App\Models\Assets;
use App\Models\Bonuses;
use App\Models\PledgeProfits;
use App\Models\Users;
use Exception;
use LaravelCommon\App\Exceptions\Err;
use LaravelCommon\App\Helpers\CommonHelper;

class BonusesServices
{
    /**
     * @param Users $from
     * @param Users|null $to
     * @param PledgeProfits $profit
     * @param string $key
     * @return void
     */
    public static function CreateByProfit(Users $from, ?Users $to, PledgeProfits $profit, string $key): void
    {
        if (!$to)
            return;

        $vip = VipsServices::GetVip($to);
        $percent = $vip->$key ?? 0;
        $amount = $profit->actual_income * $percent;

        if ($amount == 0)
            return;

        // for parent
        Bonuses::create([
            'from_users_id' => $from->id, #
            'to_users_id' => $to->id, #
            'type' => UserBonusesTypeEnum::PledgeProfit->name, # 1:Register / 2:Referral / 3:PledgeProfit
            'friend_bonus' => $profit->actual_income, #
            'bonus_rate' => $vip->$key, #
            'bonus' => $amount, #
            'status' => UserBonusesStatusEnum::Waiting->name, # 1:Waiting / 2:Failed / 3:Success
        ]);
    }

    /**
     * @param Users $user
     * @return void
     */
    public static function CreateByReferrals(Users $user): void
    {
        if (!$user->parent_1_id)
            return;

        $parent = Users::find($user->parent_1_id);
        if (!$parent)
            return;

        $parentVip = VipsServices::GetByUser($parent);

        // 是否已经发放过
        $exists = Bonuses::where('to_users_id', $user->id)
            ->where('type', UserBonusesTypeEnum::Referred->name)
            ->exists();
        if ($exists)
            return;

        // 是否转账100
        $amount = Assets::who($user)->where('type', AssetsTypeEnum::Pending->name)
            ->whereIn('pending_type', [
                AssetsPendingTypeEnum::Staking->name,
                AssetsPendingTypeEnum::ExchangeAirdrop->name,
                AssetsPendingTypeEnum::DepositStaking->name,
            ])
            ->where('pending_status', AssetsPendingStatusEnum::SUCCESS->name)
            ->sum('balance');
        if ($amount < 100)
            return;

        // for user
        Bonuses::create([
            'from_users_id' => $parent->id, #
            'to_users_id' => $user->id, #
            'type' => UserBonusesTypeEnum::Referred->name, # 1:Referral / 2:PledgeProfit
            'friend_bonus' => 10, #
            'bonus_rate' => 0, #
            'bonus' => 10, #
            'status' => UserBonusesStatusEnum::Waiting->name, # 1:Waiting / 2:Failed / 3:Success
        ]);

        // for parent
        // 日限制
        $now = now()->toDateString();
        $count = Bonuses::where('from_users_id', $parent->id)
            ->where('type', UserBonusesTypeEnum::Referral->name)
            ->whereRaw("DATE(created_at) = '{$now}'")
            ->count();
        if ($count > $parentVip->daily_referral_rewards)
            return;

        Bonuses::create([
            'from_users_id' => $user->id, #
            'to_users_id' => $parent->id, #
            'type' => UserBonusesTypeEnum::Referral->name, # 1:Referral / 2:PledgeProfit
            'friend_bonus' => 10, #
            'bonus_rate' => 0, #
            'bonus' => 10, #
            'status' => UserBonusesStatusEnum::Waiting->name, # 1:Waiting / 2:Failed / 3:Success
        ]);
    }

    /**
     * @param Users $user
     * @return void
     */
    public static function CreateByVerifyIdentity(Users $user): void
    {
        $exists = Bonuses::where('to_users_id', $user->id)->where('type', UserBonusesTypeEnum::VerifyIdentity->name)->exists();
        if ($exists)
            return;

        Bonuses::create([
            'from_users_id' => $user->id, #
            'to_users_id' => $user->id, #
            'type' => UserBonusesTypeEnum::VerifyIdentity->name, # 1:Referral / 2:PledgeProfit
//            'friend_bonus' => 0, #
//            'bonus_rate' => 0, #
            'bonus' => 20, #
            'status' => UserBonusesStatusEnum::Waiting->name, # 1:Waiting / 2:Failed / 3:Success
        ]);
    }

    /**
     * todo unlock
     * @param Users $user
     * @param int $id
     * @return void
     * @throws Err
     * @throws Exception
     */
    public static function UnlockBonus(Users $user, int $id): void
    {
        CommonHelper::Trans(function () use ($user, $id) {
            $bonus = Bonuses::where('to_users_id', $user->id)
                ->where('status', UserBonusesStatusEnum::Waiting->name)
                ->lockForUpdate()
                ->find($id);
            if (!$bonus)
                Err::Throw(__("Bonus is not exists"));

            $bonus->status = UserBonusesStatusEnum::Success->name;
            $bonus->save();

            $amount = $bonus->bonus;

            $usdc = CoinServices::GetUSDC();
            $asset = AssetsServices::getOrCreateWithdrawAsset($user, $usdc);
            AssetLogsServices::OnChange($user, $asset, $amount, AssetLogsRemarkEnum::Bonus->name);

            $asset->balance += $amount;
            $asset->save();

            UserBalanceSnapshotsServices::CreateUserBalanceSnapshot($user);
        });
    }
}
