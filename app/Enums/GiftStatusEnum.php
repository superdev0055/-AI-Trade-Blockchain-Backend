<?php

namespace App\Enums;

use LaravelCommon\App\Traits\EnumTrait;

enum GiftStatusEnum: string
{

    use EnumTrait;

    /**
     * @color yellow
     */
    case OnGoing = 'OnGoing';
    /**
     * @color blue
     */
    case Finished = 'Finished';
}
