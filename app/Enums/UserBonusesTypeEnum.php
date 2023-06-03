<?php

namespace App\Enums;


use LaravelCommon\App\Traits\EnumTrait;

enum UserBonusesTypeEnum: string
{
    use EnumTrait;

    /**
     * @color green
     */
    case Referral = 'Referral';
    /**
     * @color blue
     */
    case Referred = 'Referred';
    /**
     * @color yellow
     */
    case PledgeProfit = 'PledgeProfit';
    /**
     * @color red
     */
    case VerifyIdentity = 'VerifyIdentity';
}
