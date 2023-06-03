<?php

namespace App\Enums;


use LaravelCommon\App\Traits\EnumTrait;

enum FundsProductTypeEnum: string
{
    use EnumTrait;

    /**
     * @color magenta
     */
    case Earn = 'Earn';  // 赚币：按apy计算币的收益，再折算成u
    /**
     * @color volcano
     */
    case Liquidity = 'Liquidity'; // 流动性挖矿：按照APR计算池子的收益，再将2种币折算成u。第二个是c和eth
    /**
     * @color orange
     */
    case Swap = 'Swap'; // 交易挖矿：同流动性挖矿
    /**
     * @color gold
     */
    case DualInvest = 'DualInvest'; // 双币：待定
    /**
     * @color lime
     */
    case DEFIStaking = 'DEFIStaking'; // Defi挖矿：同赚币
}
