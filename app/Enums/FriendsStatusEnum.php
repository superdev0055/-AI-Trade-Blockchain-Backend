<?php

namespace App\Enums;

use LaravelCommon\App\Traits\EnumTrait;

enum FriendsStatusEnum: int
{
    use EnumTrait;

    /**
     * @color gray
     */
    case No = 0;
    /**
     * @color green
     */
    case Yes = 1;
    /**
     * @color red
     */
    case Both = 2;
}
