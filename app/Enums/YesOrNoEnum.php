<?php

namespace App\Enums;


use LaravelCommon\App\Traits\EnumTrait;

enum YesOrNoEnum: int
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
}
