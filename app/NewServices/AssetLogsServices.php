<?php

namespace App\NewServices;

use App\Models\AssetLogs;
use App\Models\Assets;
use App\Models\Users;

class AssetLogsServices
{
    /**
     * 记录日志
     * @param Users $user
     * @param Assets $asset
     * @param float $amount
     * @param string $remark
     * @param string|null $reason
     * @return void
     */
    public static function OnChange(Users $user, Assets $asset, float $amount, string $remark, ?string $reason = null): void
    {
        AssetLogs::create([
            'users_id' => $user->id, #
            'assets_id' => $asset->id, #
            'type' => $asset->type, #
            'before' => $asset->balance, #
            'amount' => $amount, #
            'after' => $asset->balance + $amount, #
            'remark' => $remark, #
            'reason' => $reason
        ]);
    }
}
