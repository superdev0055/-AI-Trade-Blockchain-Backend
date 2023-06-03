<?php

namespace App\Enums;


use LaravelCommon\App\Traits\EnumTrait;

enum AssetsPendingStatusEnum: string
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
     * @color #f1f1f1
     */
    case EXPIRED = 'EXPIRED';
    /**
     * @color #F50
     */
    case REJECTED = 'REJECTED';
    /**
     * @color pink
     */
    case APPROVE = 'APPROVE';
}
