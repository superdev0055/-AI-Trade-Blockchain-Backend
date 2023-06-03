<?php

namespace App\Enums;


use LaravelCommon\App\Traits\EnumTrait;

enum JackpotsStatusEnum: string
{
    use EnumTrait;

    /**
     * @color green
     */
    case OnGoing = 'OnGoing';
    /**
     * @color gray
     */
    case Finished = 'Finished';
}
