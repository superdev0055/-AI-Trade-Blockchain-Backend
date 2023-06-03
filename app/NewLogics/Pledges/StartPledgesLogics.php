<?php

namespace App\NewLogics\Pledges;

use App\Models\Funds;
use App\Models\Pledges;
use App\Models\Users;
use App\Models\Vips;
use App\NewServices\CoinServices;
use App\NewServices\ConfigsServices;
use Exception;
use LaravelCommon\App\Exceptions\Err;
use LaravelCommon\App\Helpers\CommonHelper;

class StartPledgesLogics
{
    /**
     * @ok
     * @param Users $user
     * @param Vips $vip
     * @return void
     * @throws Exception
     */
    public static function StartTrail(Users $user, Vips $vip): void
    {
        $user = Users::where('id', $user->id)->lockForUpdate()->first();
        if ($user->trailed_at)
            Err::Throw(__("You are already trailed"), 10006);
        $config = ConfigsServices::Get('trail');
        $user->trailed_at = now()->toDateTimeString();
        $user->leverage = $config['leverage'];
        $user->duration = $config['duration'];
        $user->can_trail_bonus = false;
        $user->can_automatic_exchange = $config['can_automatic_exchange'];
        $user->can_leveraged_investment = $config['can_leveraged_investment'];
        $user->can_automatic_loan_repayment = $config['can_automatic_loan_repayment'];
        $user->can_prevent_liquidation = $config['can_prevent_liquidation'];
        $user->can_profit_guarantee = $config['can_profit_guarantee'];
        $user->can_automatic_airdrop_bonus = $config['can_automatic_airdrop_bonus'];
        $user->can_automatic_staking = $config['can_automatic_staking'];
        $user->can_automatic_withdrawal = $config['can_automatic_withdrawal'];
        $user->save();

        self::createPledgeAndFunds($user, $config['amount'], true);
    }

    /**
     * @param Users $user
     * @param float $amount
     * @return void
     * @throws Err
     */
    public static function StartCommon(Users $user, float $amount): void
    {
        self::createPledgeAndFunds($user, $amount);
    }

    /**
     * @ok
     * @param Users $user
     * @param float $staking
     * @param bool $isTrail
     * @return void
     * @throws Err
     */
    private static function createPledgeAndFunds(Users $user, float $staking, bool $isTrail = false): void
    {
        $data = [
            'users_id' => $user->id, #
            'is_trail' => $isTrail, #
            'started_at' => now()->toDateTimeString(), #
            'ended_at' => now()->addDays($user->duration)->startOfDay()->addHours(18)->toDateTimeString(), #
//            'canceled_at' => '', #
            'staking' => $staking, #
            'estimate_apy' => .1, #
//            'actual_apy' => '', #
//            'earnings_this_node' => '', #
//            'earnings_today' => '', #
//            'auto_joined_funds' => '', #
//            'status' => '', # 1:OnGoing / 2:Canceled / 3:Finished
        ];
        $pledge = Pledges::create($data);
        self::createOutFunds($user, $pledge);
    }

    /**
     * @ok
     * @param Users $user
     * @param Pledges $pledge
     * @return void
     * @throws Err
     * @throws Exception
     */
    private static function createOutFunds(Users $user, Pledges $pledge): void
    {
        // 随机选择
        $funds = Funds::query()
            ->with('main_coin')
            ->inRandomOrder()
            ->take(4)
            ->get();

        $sync = [];
        $index = 0;
        foreach ($funds as $fund) {
            $sync[$fund->id] = [
                'users_id' => $user->id,
                'main_coins_id' => $fund->main_coins_id, #
                'sub_coins_id' => $fund->sub_coins_id, #
                'profits' => $fund->profits, #
                'main_coin_price' => CoinServices::GetPrice($fund->main_coin?->symbol), #
            ];
            $index++;
        }
        $pledge->funds()->sync($sync);
    }
}
