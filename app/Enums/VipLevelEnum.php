<?php

namespace App\Enums;

enum VipLevelEnum: int
{
    /**
     * @color gray
     */
    case VIP0 = 1;
    /**
     * @color green
     */
    case VIP1 = 2;
    /**
     * @color yellow
     */
    case VIP2 = 3;
    /**
     * @color red
     */
    case VIP3 = 4;
    /**
     * @color blue
     */
    case VIP4 = 5;
    /**
     * @color purple
     */
    case VIP5 = 6;
    /**
     * @color orange
     */
    case VIP6 = 7;
}
