<?php

namespace App\Enums;


use LaravelCommon\App\Traits\EnumTrait;

enum AssetsTypeEnum: string
{
    use EnumTrait;

    /**
     * @color green
     */
    case Staking = 'Staking';
    /**
     * @color yellow
     */
    case WithdrawAble = 'WithdrawAble';
    /**
     * @color red
     */
    case Pending = 'Pending';
    /**
     * @color blue
     */
    case Airdrop = 'Airdrop';
}
