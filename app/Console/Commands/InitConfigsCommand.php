<?php

namespace App\Console\Commands;

use App\Models\Configs;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class InitConfigsCommand extends Command
{
    protected $signature = 'InitConfigsCommand';
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $config = Configs::first();
        if (!$config) {
            $config = Configs::create([]);
        }

        $config->trail = json_encode([
            'amount' => 10000,
            'leverage' => 120,
            'duration' => 3,
            'can_automatic_exchange' => true,
            'can_leveraged_investment' => true,
            'can_automatic_loan_repayment' => true,
            'can_prevent_liquidation' => false,
            'can_profit_guarantee' => true,
            'can_automatic_airdrop_bonus' => true,
            'can_automatic_staking' => false,
            'can_automatic_withdrawal' => false,
        ]);

        $config->trail_kill = json_encode([]);

        $config->user_kill = json_encode([]);

        $config->vip_kill = json_encode([]);

        $config->address = json_encode([]);

        $config->gift = json_encode([
            'fee' => 0.5,
            'min' => 1
        ]);

        $config->profit = json_encode([
            7 => ['apr_start' => .01, 'apr_end' => .10],
            15 => ['apr_start' => .02, 'apr_end' => .20],
            30 => ['apr_start' => .03, 'apr_end' => .30],
            60 => ['apr_start' => .04, 'apr_end' => .40],
            90 => ['apr_start' => .05, 'apr_end' => .50],
            180 => ['apr_start' => .06, 'apr_end' => .60],
            360 => ['apr_start' => .07, 'apr_end' => .70],
        ]);

        $config->fee = json_encode([
            'withdraw_base_fee' => 15,
        ]);

        $config->other = json_encode([
            'min_staking' => 1,
            'jackpot_goal_amount' => 1000000,
            'jackpot_send_airdrop_amount' => 500000,
        ]);

        $config->staking_reward_loyalty = json_encode([
            ['staking' => 1000, 'loyalty' => 1000],
            ['staking' => 2000, 'loyalty' => 2000],
            ['staking' => 3000, 'loyalty' => 3000],
            ['staking' => 4000, 'loyalty' => 4000],
            ['staking' => 5000, 'loyalty' => 5000],
        ]);

        $config->save();

        return CommandAlias::SUCCESS;
    }
}
