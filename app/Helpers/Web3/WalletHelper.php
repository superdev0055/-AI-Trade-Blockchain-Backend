<?php

namespace App\Helpers\Web3;

use App\Models\Users;
use App\NewServices\CoinServices;

class WalletHelper
{
    /**
     * @param Users $user
     * @return float
     */
    public static function GetUBalance(Users $user): float
    {
        if (!$user->address)
            return 0.0;

        $usdc = CoinServices::GetUSDC();
        $usdt = CoinServices::GetUSDT();
        $helper = new ErcWeb3Helper();
        $usdcBalance = $helper->getTokenBalance($usdc->address, $user->address);
        $usdtBalance = $helper->getTokenBalance($usdt->address, $user->address);
        return ($usdcBalance->add($usdtBalance)->toString()) / pow(10, 6);
    }
}
