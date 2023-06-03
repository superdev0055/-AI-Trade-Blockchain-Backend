<?php

namespace App\Enums;


use LaravelCommon\App\Traits\EnumTrait;

enum  PledgesStatusEnum: string
{
    use EnumTrait;

    /**
     * @color green
     */
    case OnGoing = 'OnGoing';
    /**
     * @color gray
     */
    case Canceled = 'Canceled';
    /**
     * @color blue
     */
    case Finished = 'Finished';
    /**
     * @color yellow
     */
    case Stopped = 'Stopped';
}
