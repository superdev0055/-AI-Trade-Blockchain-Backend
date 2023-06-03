<?php

namespace App\Enums;

use LaravelCommon\App\Traits\EnumTrait;

enum AssetLogsTypeEnum: string
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
}
