<?php

namespace App\Enums;


use LaravelCommon\App\Traits\EnumTrait;

enum UserBonusesStatusEnum: string
{
    use EnumTrait;

    /**
     * @color yellow
     */
    case Waiting = 'Waiting';
    /**
     * @color red
     */
    case Failed = 'Failed';
    /**
     * @color green
     */
    case Success = 'Success';
}
