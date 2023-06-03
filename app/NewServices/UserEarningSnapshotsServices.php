<?php

namespace App\NewServices;

use App\Models\UserEarningSnapshots;
use App\Models\Users;
use JetBrains\PhpStorm\ArrayShape;

class UserEarningSnapshotsServices
{
    /**
     * @param Users $user
     * @return array
     */
    public static function GetTop200EarningSparkline(Users $user): array
    {
        $list = UserEarningSnapshots::selectRaw('datetime,earning')
            ->where('users_id', $user->id)
            ->orderByDesc('id')
            ->take(200)
            ->get()
            ->toArray();

        return array_reverse($list);
    }

    /**
     * @todo total_rate 如何计算？
     * @param Users $user
     * @return array[]
     */
    #[ArrayShape(['header' => "array", 'data' => "array"])]
    public static function GetAllEarningSparkline(Users $user): array
    {
        $all = UserEarningSnapshots::selectRaw('datetime, earning')
            ->where('users_id', $user->id);

        return [
            'header' => [
                'balance' => $user->total_actual_income,
                'rate' => $user->total_rate,
            ],
            'data' => [
                '1D' => (clone $all)->whereBetween('created_at', [now()->subDay()->startOfDay()->toDateTimeString(), now()->endOfDay()->toDateTimeString()])->get()->toArray(),
                '1W' => (clone $all)->whereBetween('created_at', [now()->subDays(7)->startOfDay()->toDateTimeString(), now()->endOfDay()->toDateTimeString()])->get()->toArray(),
                '1M' => (clone $all)->whereBetween('created_at', [now()->subDays(30)->startOfDay()->toDateTimeString(), now()->endOfDay()->toDateTimeString()])->get()->toArray(),
                '1Y' => (clone $all)->whereBetween('created_at', [now()->subDays(365)->startOfDay()->toDateTimeString(), now()->endOfDay()->toDateTimeString()])->get()->toArray(),
                'ALL' => (clone $all)->get()->toArray(),
            ]
        ];
    }

    /**
     * @param Users $user
     * @param float $actual_income
     * @return void
     */
    public static function CreateUserEarningSnapshot(Users $user, float $actual_income): void
    {
        UserEarningSnapshots::create([
            'users_id' => $user->id, #
            'datetime' => now()->toDateTimeString(), #
            'earning' => $actual_income, #
        ]);
    }
}
