<?php

namespace App\Enums;

use LaravelCommon\App\Traits\EnumTrait;

enum ETHSymbolEnum: string
{
    use EnumTrait;

    /**
     * @color green
     */
    case USDT = 'USDT';
    /**
     * @color blue
     */
    case USDC = 'USDC';
}
