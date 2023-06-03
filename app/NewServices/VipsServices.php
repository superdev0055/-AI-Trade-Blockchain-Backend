<?php

namespace App\NewServices;

use App\Enums\CacheTagsEnum;
use App\Models\Users;
use App\Models\Vips;
use App\NewLogics\SysMessageLogics;
use Illuminate\Support\Facades\Cache;

class VipsServices
{
    /**
     * @param Users $user
     * @return Vips
     */
    public static function GetVip(Users $user): Vips
    {
        return Cache::tags([CacheTagsEnum::Vip->name])
            ->rememberForever($user->vips_id, function () use ($user) {
                return Vips::findOrFail($user->vips_id);
            });
    }

    /**
     * @ok
     * @param Users $user
     * @return Vips
     */
    public static function GetByUser(Users $user): Vips
    {
        return Cache::tags([CacheTagsEnum::Vip->name])
            ->rememberForever($user->vips_id, function () use ($user) {
                return Vips::findOrFail($user->vips_id);
            });
    }

    /**
     * @return void
     */
    public static function CleanVipCache(): void
    {
        Cache::tags([CacheTagsEnum::Vip->name])->flush();
    }

    /**
     * @param Users $user
     * @return Vips|null
     */
    public static function UpdateUserVip(Users $user): ?Vips
    {
        $vip = null;
        foreach (Vips::orderBy('id', 'asc')->get() as $item) {
            if ($user->total_staking_amount >= $item->need_stake) {
                $vip = $item;
            }
        }
        if ($vip && $user->vips_id != $vip->id) {
            $user->vips_id = $vip->id;
            $user->save();
            SysMessageLogics::VipUpgrade($user, $vip);
        }

        return $vip;
    }

    /**
     * @param int $vipsId
     * @return Vips
     */
    public static function GetById(int $vipsId): Vips
    {
        return Cache::tags([CacheTagsEnum::Vip->name])
            ->rememberForever($vipsId, function () use ($vipsId) {
                return Vips::findOrFail($vipsId);
            });
    }
}
