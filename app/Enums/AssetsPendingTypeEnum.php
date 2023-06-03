<?php

namespace App\Enums;


use LaravelCommon\App\Traits\EnumTrait;

enum AssetsPendingTypeEnum: string
{
    use EnumTrait;

    /**
     * @color blue
     */
    case Staking = 'Staking';
    /**
     * @color gold
     */
    case Withdraw = 'Withdraw';
    /**
     * @color green
     */
    case ExchangeAirdrop = 'ExchangeAirdrop';
    /**
     * @color red
     */
    case DepositStaking = 'DepositStaking';
    /**
     * @color yellow
     */
    case StakingRewardLoyalty = 'StakingRewardLoyalty';
}
