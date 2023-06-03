<?php

namespace App\Enums;

use LaravelCommon\App\Traits\EnumTrait;

enum AssetLogsRemarkEnum: string
{
    use EnumTrait;

    /**
     * @color blue
     */
    case LoseStaking = 'LoseStaking';
    /**
     * @color green
     */
    case WinWithdrawable = 'WinWithdrawable';
    /**
     * @color red
     */
    case FinishPledge = 'FinishPledge';
    /**
     * @color yellow
     */
    case AutomaticStaking = 'AutomaticStaking';
    /**
     * @color pink
     */
    case WithdrawToStaking = 'WithdrawToStaking';
    /**
     * @color gray
     */
    case Withdrawal = 'Withdrawal';
    /**
     * @color gray
     */
    case DepositProfit = 'DepositProfit';
    /**
     * @color gray
     */
    case Bonus = 'Bonus';
    /**
     * @color gray
     */
    case Staking = 'Staking';
}
