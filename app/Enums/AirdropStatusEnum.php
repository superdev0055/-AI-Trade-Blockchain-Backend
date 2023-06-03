<?php

namespace App\Enums;


use LaravelCommon\App\Traits\EnumTrait;

enum AirdropStatusEnum: string
{
    use EnumTrait;

    /**
     * @color gray
     */
    case NotReady = 'NotReady';
    /**
     * @color blue
     */
    case Ready = 'Ready';
    /**
     * @color yellow
     */
    case Expired = 'Expired';
    /**
     * @color green
     */
    case Finished = 'Finished';
}
