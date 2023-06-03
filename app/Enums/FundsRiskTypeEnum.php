<?php

namespace App\Enums;


use LaravelCommon\App\Traits\EnumTrait;

enum FundsRiskTypeEnum: string
{
    use EnumTrait;

    /**
     * @color green
     */
    case Protected = 'Protected';  // 保本型
    /**
     * @color red
     */
    case HighYield = 'HighYield'; // 高收益型
}
