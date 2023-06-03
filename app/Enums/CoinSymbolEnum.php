<?php

namespace App\Enums;

use LaravelCommon\App\Traits\EnumTrait;

enum CoinSymbolEnum: string
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
    /**
     * @color gray
     */
    case ETH = 'ETH';
    /**
     * @color yellow
     */
    case TRX = 'TRX';
}
