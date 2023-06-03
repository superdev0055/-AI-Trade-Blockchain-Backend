<?php

namespace App\NewServices;

use App\Enums\AssetsTypeEnum;
use App\Models\Assets;
use App\Models\UserBalanceSnapshots;
use App\Models\Users;
use Exception;

class UserBalanceSnapshotsServices
{
    /**
     * @param $user
     * @return void
     * @throws Exception
     */
    public static function CreateUserBalanceSnapshot($user): void
    {
        $usd = 0;

        // staking + withdrawal
        Assets::who($user)
            ->whereIn('type', [AssetsTypeEnum::Staking->name, AssetsTypeEnum::WithdrawAble->name])
            ->each(function ($item) use (&$usd) {
                if ($item->balance == 0)
                    return;
                if ($item->symbol == 'usdc' || $item->symbol == 'usdt') {
                    $usd += $item->balance;
                } else {
                    $price = CoinServices::GetPrice($item->symbol);
                    $usd += $price * $item->balance;
                }
            });

        UserBalanceSnapshots::create([
            'users_id' => $user->id, #
            'datetime' => now()->toDateTimeString(), #
            'balance' => $usd, #
        ]);

        $user->total_balance = $usd;
        $user->total_rate = $user->total_balance ? $user->total_income / $user->total_balance : 0;
        $user->save();
    }
}
