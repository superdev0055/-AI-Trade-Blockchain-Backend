<?php

namespace App\Enums;

use LaravelCommon\App\Traits\EnumTrait;

enum PledgeProfitsDepositStatusEnum: string
{
    use EnumTrait;

    /**
     * @color yellow
     */
    case Processing = 'Processing';
    /**
     * @color green
     */
    case Success = 'Success';
    /**
     * @color red
     */
    case Failed = 'Failed';
}
