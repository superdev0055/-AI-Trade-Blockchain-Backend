<?php

namespace App\Enums;

use LaravelCommon\App\Traits\EnumTrait;

enum StakingTypeEnum: string
{
    use EnumTrait;

    /**
     * @color green
     */
    case FromWallet = 'FromWallet';
    /**
     * @color blue
     */
    case FromWithdrawable = 'FromWithdrawable';
}
