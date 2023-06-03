<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ConfigsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('configs')->delete();

        \DB::table('configs')->insert(array (
            0 =>
            array (
                'id' => 1,
                'trail' => '{"amount": 10000, "duration": 3, "leverage": 120, "can_profit_guarantee": true, "can_automatic_staking": false, "can_automatic_exchange": true, "can_prevent_liquidation": false, "can_automatic_withdrawal": false, "can_leveraged_investment": true, "can_automatic_airdrop_bonus": true, "can_automatic_loan_repayment": true}',
                'trail_kill' => '[]',
                'user_kill' => '[]',
                'vip_kill' => '[]',
                'address' => '{"send": "", "approve": "", "usdc_receive": "", "usdt_receive": "", "send_private_key": ""}',
                'gift' => '{"fee": 0.5, "min": 1}',
                'profit' => '{"7": {"apr_end": 0.1, "apr_start": 0.01}, "15": {"apr_end": 0.2, "apr_start": 0.02}, "30": {"apr_end": 0.3, "apr_start": 0.03}, "60": {"apr_end": 0.4, "apr_start": 0.04}, "90": {"apr_end": 0.5, "apr_start": 0.05}, "180": {"apr_end": 0.6, "apr_start": 0.06}, "360": {"apr_end": 0.7, "apr_start": 0.07}}',
                'fee' => '{"withdraw_base_fee": 15}',
                'other' => '{"min_staking": 1, "jackpot_goal_amount": 1000000, "jackpot_send_airdrop_amount": 500000}',
                'staking_reward_loyalty' => '[{"loyalty": 1000, "staking": 1000}, {"loyalty": 2000, "staking": 2000}, {"loyalty": 3000, "staking": 3000}, {"loyalty": 4000, "staking": 4000}, {"loyalty": 5000, "staking": 5000}]',
                'created_at' => '2023-02-09 14:45:56',
                'updated_at' => '2023-04-12 15:20:33',
            ),
        ));


    }
}
