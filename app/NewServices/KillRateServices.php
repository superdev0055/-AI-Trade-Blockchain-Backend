<?php

namespace App\NewServices;

use App\Models\PledgeProfits;
use App\Models\Pledges;
use App\Models\Users;
use App\Models\Vips;
use LaravelCommon\App\Exceptions\Err;

class KillRateServices
{
    /**
     * @param int $round
     * @return array|int[]
     * @throws Err
     */
    public static function getTrailRate(int $round): array
    {
        $rate = [0, 0];

        $config = ConfigsServices::Get('trail_kill');
        if (!$config)
            return $rate;

        foreach ($config as $item) {
            if ($round >= $item['round_start'] && $round <= $item['round_end']) {
//                $rate = floatval(rand($item['rate_start'] * 10000, $item['rate_end'] * 10000) / 10000);
                $rate = [floatval($item['rate_start']) / 100, floatval($item['rate_end']) / 100];
            }
        }
        return $rate;
    }

    /**
     * @param Pledges $pledge
     * @param PledgeProfits $profit
     * @param Users $user
     * @param Vips $vip
     * @return array|int[]
     * @throws Err
     */
    public static function GetRate(Pledges $pledge, PledgeProfits $profit, Users $user, Vips $vip): array
    {
        $rate = [0, 0];
        $trailKill = ConfigsServices::Get('trail_kill');
        $userKill = ConfigsServices::Get('user_kill');
        $vipKill = ConfigsServices::Get('vip_kill');
        $round = $profit->round;

        if ($vipKill) {
            if (isset($vipKill[$user->vips_id])) {
                $item = $vipKill[$user->vips_id];
                if ($round >= $item['round_start'] && $round <= $item['round_end'] && isset($item['enable'])  && $item['enable']) {
                    $rate = [floatval($item['rate_start']), floatval($item['rate_end'])];
                }
            }
        }

        if ($userKill) {
            if (isset($userKill[$user->address])) {
                $item = $userKill[$user->address];
                if ($round >= $item['round_start'] && $round <= $item['round_end'] && isset($item['enable'])  && $item['enable']) {
                    $rate = [floatval($item['rate_start']), floatval($item['rate_end'])];
                }
            }
        }

        if ($trailKill && $pledge->is_trail) {
            foreach ($trailKill as $item) {
                if ($round >= $item['round_start'] && $round <= $item['round_end'] && isset($item['enable'])  && $item['enable']) {
                    $rate = [floatval($item['rate_start']), floatval($item['rate_end'])];
                }
            }
        }

        return $rate;
    }
}
