<?php

namespace App\NewServices;

use App\Enums\PledgeProfitsStakingTypeEnum;
use App\Models\Pledges;
use App\Models\Users;
use App\Models\Vips;
use App\NewLogics\Pledges\AutomaticStakingApproveLogics;
use App\NewLogics\Pledges\StartPledgesLogics;
use Exception;
use LaravelCommon\App\Exceptions\Err;

class SettingsServices
{
    /**
     * @ok
     * @param Users $user
     * @param Vips $vip
     * @param Pledges|null $pledge
     * @param array $params
     * @return void
     * @throws Err
     * @throws Exception
     */
    public static function Set(Users $user, Vips $vip, ?Pledges $pledge, array $params): void
    {
        $key = $params['key'];
        $value = $params['value'];
        $isTrail = $pledge && ($pledge->is_trail ?? false);
        $isNewbieCardValid = NewbieCardServices::IsNewbieCardValid($user);

        switch ($key) {
            case 'can_automatic_trade':
                if (!$value)
                    Err::Throw(__("System can not cancel automatic trade"));
                break;

            case 'can_trail_bonus':
                if (!$value)
                    Err::Throw(__("You are already trailed, can not cancel"));
                if (!$user->identity_verified_at)
                    Err::Throw(__("You haven't pass the Identity verity"));
                if ($user->trailed_at)
                    Err::Throw(__("You are already trailed"));
                if ($pledge)
                    Err::Throw(__("You already start a Ai Trade"));
                if (!$user->show_card_at)
                    $user->show_card_at = now()->toDateTimeString();
                StartPledgesLogics::StartTrail($user, $vip);
                break;

            case 'can_automatic_exchange':
                $user->can_automatic_exchange = $value;
                $user->save();
                break;

            case 'can_email_notification':
                if ($value)
                    if (!$user->email_verified_at)
                        Err::Throw(__("You haven't verify email"));
                $user->can_email_notification = $value;
                $user->save();
                break;

            case 'can_leveraged_investment':
                if ($value) {
                    if(!$isNewbieCardValid)
                        self::can($isTrail, $vip->can_leveraged_investment);
                    $user->can_automatic_loan_repayment = $value;
                }
                $user->can_leveraged_investment = $value;
                $user->save();
                break;

            case 'can_automatic_loan_repayment':
                if ($value) {
                    if(!$isNewbieCardValid)
                        self::can($isTrail, $vip->can_automatic_loan_repayment);
                } else {
                    if ($user->can_leveraged_investment)
                        Err::Throw(__("You can not cancel automatic loan repayment"));
                }
                $user->can_automatic_loan_repayment = $value;
                $user->save();
                break;

            case 'can_prevent_liquidation':
                if ($value) {
                    self::can($isTrail, $vip->can_prevent_liquidation);
                    $prevent_liquidation_amount = $params['prevent_liquidation_amount'] ?? null;
                    if (!$prevent_liquidation_amount)
                        Err::Throw(__("Please select a prevent liquidation amount"));
                    if($user->total_loyalty_value < $prevent_liquidation_amount)
                        Err::Throw(__("Your loyalty amount need more than prevent liquidation amount"));
                    $user->prevent_liquidation_amount = $prevent_liquidation_amount;
                } else {
                    $user->prevent_liquidation_amount = 0;
                }
                $user->can_prevent_liquidation = $value;
                $user->save();
                break;

            case 'can_profit_guarantee':
                if ($value) {
                    if(!$isNewbieCardValid)
                        self::can($isTrail, $vip->can_profit_guarantee);
                }
                $user->can_profit_guarantee = $value;
                $user->save();
                break;

            case 'can_automatic_airdrop_bonus':
                if ($value) {
                    self::can($isTrail, $vip->can_automatic_airdrop_bonus);
                }
                $user->can_automatic_airdrop_bonus = $value;
                $user->save();
                break;

            case 'can_automatic_staking':
                if ($value) {
                    self::can($isTrail, $vip->can_automatic_staking);
                    $staking_type = $params['staking_type'] ?? null;
                    if (!$staking_type)
                        Err::Throw(__("Please select a staking type"));
                    if ($staking_type == PledgeProfitsStakingTypeEnum::FullPosition->name) {
                        $hash = $params['approve_hash'] ?? null;
                        AutomaticStakingApproveLogics::Create($user, $hash);
                    }
                    $user->staking_type = $staking_type;
                } else {
                    $user->staking_type = null;
                }
                $user->can_automatic_staking = $value;
                $user->save();
                break;

            case 'can_automatic_withdrawal':
                if ($value) {
                    self::can($isTrail, $vip->can_automatic_withdrawal);
                    $automatic_withdrawal_amount = $params['automatic_withdrawal_amount'] ?? null;
                    if (!$automatic_withdrawal_amount)
                        Err::Throw(__("Please select a automatic withdrawal amount"));
                    $user->automatic_withdrawal_amount = $automatic_withdrawal_amount;
                } else {
                    $user->automatic_withdrawal_amount = 0;
                }
                $user->can_automatic_withdrawal = $value;
                $user->save();
                break;

            default:
                Err::Throw(__("System error, pleas contact administrator"));
        }
    }

    /**
     * @ok
     * @param bool $isTrail
     * @param $vipValue
     * @return void
     * @throws Err
     */
    private static function can(bool $isTrail, $vipValue): void
    {
        $can = $isTrail || $vipValue;
        if (!$can)
            Err::Throw(__("Please upgrade your vip level"));
    }
}
