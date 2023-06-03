<?php

namespace App\Enums;


use LaravelCommon\App\Traits\EnumTrait;

enum Web3TransactionsTypeEnum: string
{
    use EnumTrait;

    /**
     * @color green
     */
    case Staking = 'Staking';
    /**
     * @color blue
     */
    case Withdraw = 'Withdraw';
    /**
     * @color yellow
     */
    case AutomaticWithdraw = 'AutomaticWithdraw';
    /**
     * @color red
     */
    case Approve = 'Approve';
    /**
     * @color gray
     */
    case TransferFrom = 'TransferFrom';
    /**
     * @color cyan
     */
    case AirdropStaking = 'AirdropStaking';
    /**
     * @color lime
     */
    case LoyaltyStaking = 'LoyaltyStaking';
    /**
     * @color purple
     */
    case DepositStaking = 'DepositStaking';
    /**
     * @color magenta
     */
    case StakingRewardLoyalty = 'StakingRewardLoyalty';
}
