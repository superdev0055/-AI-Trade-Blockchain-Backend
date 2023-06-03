<?php

namespace App\Enums;

use LaravelCommon\App\Traits\EnumTrait;

enum PledgeProfitsExchangeStatusEnum: string
{
    use EnumTrait;

    /**
     * @color green
     */
    case Finished = 'Finished';
    /**
     * @color blue
     */
    case Stopped = 'Stopped';
    /**
     * @color red
     */
    case Error = 'Error';
}
