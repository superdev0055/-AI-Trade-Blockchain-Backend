<?php

namespace App\Enums;

use LaravelCommon\App\Traits\EnumTrait;

enum CoinNetworkEnum: string
{
    use EnumTrait;

    /**
     * @color gray
     */
    case ERC20 = 'ERC20';
    /**
     * @color red
     */
    case TRC20 = 'TRC20';
}
