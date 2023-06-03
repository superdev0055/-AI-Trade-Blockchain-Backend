<?php

namespace App\Enums;


use LaravelCommon\App\Traits\EnumTrait;

enum UsersStatusEnum: string
{
    use EnumTrait;

    /**
     * @color gray
     */
    case Disable = 'Disable';
    /**
     * @color green
     */
    case Enable = 'Enable';
}
