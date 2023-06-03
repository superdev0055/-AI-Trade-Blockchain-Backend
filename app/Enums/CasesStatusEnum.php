<?php

namespace App\Enums;


use LaravelCommon\App\Traits\EnumTrait;

enum CasesStatusEnum: string
{
    use EnumTrait;

    /**
     * @color yellow
     */
    case Pending = 'Pending';
    /**
     * @color gray
     */
    case Closed = 'Closed';
}
