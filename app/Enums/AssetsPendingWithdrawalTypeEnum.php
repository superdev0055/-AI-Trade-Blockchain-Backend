<?php

namespace App\Enums;

use LaravelCommon\App\Traits\EnumTrait;

enum AssetsPendingWithdrawalTypeEnum: string
{
    use EnumTrait;

    /**
     * @color red
     */
    case Automatic = 'Automatic';
    /**
     * @color blue
     */
    case Manual = 'Manual';
}
