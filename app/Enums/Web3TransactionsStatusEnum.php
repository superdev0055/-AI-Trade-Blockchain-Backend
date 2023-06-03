<?php

namespace App\Enums;


use LaravelCommon\App\Traits\EnumTrait;

enum Web3TransactionsStatusEnum: string
{
    use EnumTrait;

    /**
     * @color gray
     */
    case WAITING = 'WAITING';
    /**
     * @color blue
     */
    case PROCESSING = 'PROCESSING';
    /**
     * @color red
     */
    case ERROR = 'ERROR';
    /**
     * @color green
     */
    case SUCCESS = 'SUCCESS';
    /**
     * @color yellow
     */
    case EXPIRED = 'EXPIRED';
    /**
     * @color orange
     */
    case REJECTED = 'REJECTED';
}
