<?php

namespace App\Enums;


use LaravelCommon\App\Traits\EnumTrait;

enum UsersIdentityStatusEnum: string
{
    use EnumTrait;

    /**
     * @color gray
     */
    case Default = 'Default';
    /**
     * @color yellow
     */
    case Waiting = 'Waiting';
    /**
     * @color green
     */
    case OK = 'OK';
    /**
     * @color red
     */
    case Failed = 'Failed';
}
