<?php

namespace App\Enums;

use LaravelCommon\App\Traits\EnumTrait;

enum GiftTypeEnum: string
{
    use EnumTrait;

    /**
     * @color red
     */
    case RandomAmount = 'RandomAmount';
    /**
     * @color blue
     */
    case FixedAmount = 'FixedAmount';
}
