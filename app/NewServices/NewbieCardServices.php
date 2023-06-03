<?php

namespace App\NewServices;

use App\Models\Users;
use LaravelCommon\App\Exceptions\Err;

class NewbieCardServices
{
    /**
     * 是否有有效的新手卡
     * @param Users $user
     * @return bool
     */
    public static function IsNewbieCardValid(Users $user): bool
    {
        if (
            $user->membership_card == 1 &&
            $user->membership_end_date > now()->toDateTimeString() &&
            $user->vips_id <= 3
        ) {
            return true;
        }
        return false;
    }

    /**
     * 新手卡的提现是否免手续费
     * @param Users $user
     * @return bool
     */
    public static function CanZeroFeeOfWithdraw(Users $user): bool
    {
        $IsNewbieCardValid = self::IsNewbieCardValid($user);
        if (!$IsNewbieCardValid)
            return false;

        return (!$user->first_withdrawal_free_date);
    }

    /**
     * @param Users $user
     * @return void
     * @throws Err
     */
    public static function UserGetNewbieCard(Users $user): void
    {
        // 是否领取过
        if ($user->membership_card)
            Err::Throw(__('You have already received the new user card'));

        // vip0才可以领取
        if ($user->vips_id != 1)
            Err::Throw(__('You are not a new user'));

        // 是否质押超过$100
        if ($user->total_staking_amount < 100)
            Err::Throw(__('You need to staking more than $100'));

        // 领取卡，设置权益
        $user->membership_card = 1;
        $user->membership_start_date = now()->toDateTimeString();
        $user->membership_end_date = now()->addDays(14)->toDateTimeString();

        $user->leverage = 60;
        $user->can_profit_guarantee = 1;
        $user->can_leveraged_investment = 1;
        $user->can_automatic_loan_repayment = 1;
        $user->first_withdrawal_free = true;
        $user->save();
    }
}
