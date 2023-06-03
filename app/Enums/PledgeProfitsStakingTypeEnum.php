<?php

namespace App\Enums;

use LaravelCommon\App\Traits\EnumTrait;

enum PledgeProfitsStakingTypeEnum: string
{
    use EnumTrait;

    /**
     * @color red
     */
    case FullPosition = 'FullPosition';
    /**
     * @color blue
     */
    case IsolatedMargin = 'IsolatedMargin';
}
