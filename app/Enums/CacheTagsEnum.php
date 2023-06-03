<?php

namespace App\Enums;

use LaravelCommon\App\Traits\EnumTrait;

enum CacheTagsEnum: string
{
    use EnumTrait;

    /**
     * @color yellow
     */
    case GetCoinsMarkets = 'GetCoinsMarkets';
    /**
     * @color red
     */
    case GetCoinsPrice = 'GetCoinsPrice';
    /**
     * @color blue
     */
    case CoinPrice = 'CoinPrice';
    /**
     * @color green
     */
    case Vip = 'Vip';
    /**
     * @color pink
     */
    case OnlineStatus = 'OnlineStatus';
}
