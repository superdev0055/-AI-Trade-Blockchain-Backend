<?php

namespace App\Modules\Customer;

use LaravelCommon\App\Exceptions\Err;
use App\Models\Vips;
use App\Modules\CustomerBaseController;
use JetBrains\PhpStorm\ArrayShape;

class VipController extends CustomerBaseController
{
    /**
     * @intro vip页面
     * @return array[]
     * @throws Err
     */
    #[ArrayShape(['user' => "array", 'levels' => "", 'vips' => "array"])]
    public function show(): array
    {
        $user = $this->getUser();
        $vip = $this->getVip();
        $nextVip = Vips::find($user->vips_id + 1);
        $allVip = Vips::all();

        // user
        $user->total_staking_amount = floatval($user->total_staking_amount);
        if ($user->total_staking_amount == 0) {
            $nextLevelNeedStake = $nextVip->need_stake;
            $levelUp = 0;
        } else if ($user->vips_id == 7) {
            $nextLevelNeedStake = 0;
            $levelUp = 100;
        } else {
            $nextLevelNeedStake = $nextVip->need_stake - $user->total_staking_amount;
            $levelUp = round(($user->total_staking_amount - $vip->need_stake) / ($nextVip->need_stake - $vip->need_stake) * 100);
        }

        // vip
        $vips = [
            [
                'items' => [
                    [
                        'name' => __("Automated trading"),
                        'key' => 'can_automatic_trade',
                        'desc' => __("Ai Trade adopts one-click automatic operation, fully intelligent analysis of the market, digital currency risks, and optimal investment probability, which is convenient and maximizes your profits."),
                        'values' => $allVip->pluck('can_automatic_trade')
                    ],
                    [
                        'name' => __("Trial bonus"),
                        'key' => 'can_trail_bonus',
                        'desc' => __("All certified members can receive a $10,000 trial bonus and can withdraw profits for three days."),
                        'values' => $allVip->pluck('can_trail_bonus')
                    ],
                    [
                        'name' => __("Automatic exchange"),
                        'key' => 'can_automatic_exchange',
                        'desc' => __("Automatically convert profitable tokens into USDC without any fee, so that you can be more aware of whether your investment is profitable, and avoid affecting the direction of investment due to the decline in the market value of tokens."),
                        'values' => $allVip->pluck('can_automatic_exchange')
                    ],
                    [
                        'name' => __("E-mail notification"),
                        'key' => 'can_email_notification',
                        'desc' => __("Binding personal mailbox, you can receive each round of profit information, deposit and withdrawal information, and important system notifications at any time to avoid irreparable losses caused by market fluctuations."),
                        'values' => $allVip->pluck('can_email_notification')
                    ],
                    [
                        'name' => __("Leveraged investment"),
                        'key' => 'can_leveraged_investment',
                        'desc' => __("VIP1 and above can open leverage, the upper limit is 125 times, and the income can be increased to 125 times, which is equivalent to a free increase of 124 times the investment principal."),
                        'values' => $allVip->pluck('can_leveraged_investment')
                    ],
                    [
                        'name' => __("Auto loan repayment"),
                        'key' => 'can_automatic_loan_repayment',
                        'desc' => __("When the leverage is turned on, the automatic loan and repayment functions will also be turned on. The whole process does not require mortgages, automatic lending, and charging of handling fees when the income is completed, which is easy and convenient."),
                        'values' => $allVip->pluck('can_automatic_loan_repayment')
                    ],
                    [
                        'name' => __("Liquidation protection"),
                        'key' => 'can_prevent_liquidation',
                        'desc' => __("When the market fluctuates violently, you can set the maximum loss amount. When the loss is greater than the set amount, the system will automatically close the position for you to avoid a single large loss."),
                        'values' => $allVip->pluck('can_prevent_liquidation')
                    ],
                    [
                        'name' => __("Profit guarantee"),
                        'key' => 'can_profit_guarantee',
                        'desc' => __("Turn on the profit guarantee. When the APY profit is lower than 5%, the system will automatically transfer the difference from the fund pool to make up your profit, ensuring that your profit per round is not less than 5%."),
                        'values' => $allVip->pluck('can_profit_guarantee')
                    ],
                    [
                        'name' => __("Automatic Airdrop Bonus"),
                        'key' => 'can_automatic_airdrop_bonus',
                        'desc' => __("Automatically participate in profit ranking activities, when the prize pool reaches 1 million USDC, it will automatically airdrop 500,000 USDC."),
                        'values' => $allVip->pluck('can_automatic_airdrop_bonus')
                    ],
                    [
                        'name' => __("Automatic staking"),
                        'key' => 'can_automatic_staking',
                        'desc' => __("VIP2 can be turned on, and the automatic staking will automatically staking the profit of this round as the investment principal of the next round, and the income obtained will change from a simple interest model to a compound interest model."),
                        'values' => $allVip->pluck('can_automatic_staking')
                    ],
                    [
                        'name' => __("Automatic withdrawal"),
                        'key' => 'can_automatic_withdrawal',
                        'desc' => __("when above VIP3, the amount of automatic withdrawal can be set. When the profit reaches the set amount, the system will automatically apply for withdrawal and automatically transfer to your designated account."),
                        'values' => $allVip->pluck('can_automatic_withdrawal')
                    ],
                ],
            ],
            [
                'items' => [
                    [
                        'name' => __("Daily Referral Rewards"),
                        'key' => 'daily_referral_rewards',
                        'desc' => __("Daily referral bonus. If the number of referrals exceeds the number of daily referrals, there will be no corresponding commission. Recommend 1 person 10USDC."),
                        'values' => $allVip->pluck('daily_referral_rewards')
                    ],
                    [
                        'name' => __("Level 1 refer (yield)"),
                        'key' => 'level_1_refer',
                        'desc' => __("For first-level referrals, you will get a percentage of his daily earnings."),
                        'values' => $allVip->pluck('level_1_refer')
                    ],
                    [
                        'name' => __("Level 2 refer (yield)"),
                        'key' => 'level_2_refer',
                        'desc' => __("For second-level referrals, you will receive a percentage of his daily earnings."),
                        'values' => $allVip->pluck('level_2_refer')
                    ],
                    [
                        'name' => __("Level 3 refer (yield)"),
                        'key' => 'level_3_refer',
                        'desc' => __("For third-level referrals, you will receive a percentage of his daily earnings."),
                        'values' => $allVip->pluck('level_3_refer')
                    ],
                    [
                        'name' => __("PM friends"),
                        'key' => 'can_pm_friends',
                        'desc' => __("Members can send private messages to designated members through the system's message."),
                        'values' => $allVip->pluck('can_pm_friends')
                    ],
                    [
                        'name' => __("Customize online status"),
                        'key' => 'can_customize_online_status',
                        'desc' => __("Members can hide automatic online status, no way to let others know you are online."),
                        'values' => $allVip->pluck('can_customize_online_status')
                    ],
                    [
                        'name' => __("View contact details"),
                        'key' => 'can_view_contact_details',
                        'desc' => __("Members can view the contact information of each other's social software and find each other through social software."),
                        'values' => $allVip->pluck('can_view_contact_details')
                    ],
                    [
                        'name' => __("Send a gift"),
                        'key' => 'can_send_gift',
                        'desc' => __("Members can choose USDC or airdrop coupons from their own assets to send gifts, and other members can receive the corresponding gifts through the link you send."),
                        'values' => $allVip->pluck('can_send_gift')
                    ],
                ]
            ],
            [
                'items' => [
                    [
                        'name' => __("Investment Leveraged"),
                        'key' => 'leveraged_investment',
                        'desc' => __("The leverage multiples that can be opened for each level are different. The higher the leverage, the higher the income."),
                        'values' => $allVip->pluck('leveraged_investment')
                    ],
                    [
                        'name' => __("Loan charges"),
                        'key' => 'loan_charges',
                        'desc' => __("The fee for the loan is based on the percentage of your current income. The higher the level, the lower the cost of the loan."),
                        'values' => $allVip->pluck('loan_charges')
                    ],
                    [
                        'name' => __("Minimum APY Guarantee"),
                        'key' => 'minimum_apy_guarantee',
                        'desc' => __("When the income is less than the minimum guarantee, Ai trade will automatically make up the income, and the higher the level, the higher the guaranteed APY."),
                        'values' => $allVip->pluck('minimum_apy_guarantee')
                    ],
                    [
                        'name' => __("Promotion first notice"),
                        'key' => 'can_promotion_first_notice',
                        'desc' => __("When high-yield and quota-limited activities, it will be pushed to higher-level members first."),
                        'values' => $allVip->pluck('can_promotion_first_notice')
                    ],
                    [
                        'name' => __("Exclusive customer service"),
                        'key' => 'can_exclusive_customer_service',
                        'desc' => __("We provide a 7*24-hour exclusive customer service manager for senior VIP members to solve all problems for members."),
                        'values' => $allVip->pluck('can_exclusive_customer_service')
                    ],
                    [
                        'name' => __("Staking term"),
                        'key' => 'max_staking_term',
                        'desc' => __("The longer the staking term is set, the more profit and the higher the stability will be obtained."),
                        'values' => $allVip->pluck('max_staking_term')
                    ],
                ]
            ],
            [
                'items' => [
                    [
                        'name' => __("Minimum withdrawal limit"),
                        'key' => 'minimum_withdrawal_limit',
                        'desc' => __("The amount of each withdrawal."),
                        'values' => $allVip->pluck('minimum_withdrawal_limit')
                    ],
                    [
                        'name' => __("Maximum withdrawal limit"),
                        'key' => 'maximum_withdrawal_limit',
                        'desc' => __("The amount of each withdrawal."),
                        'values' => $allVip->pluck('maximum_withdrawal_limit')
                    ],
                    [
                        'name' => __("Number of withdrawals"),
                        'key' => 'number_of_withdrawals',
                        'desc' => __("Limit on the number of daily withdrawals."),
                        'values' => $allVip->pluck('number_of_withdrawals')
                    ],
                    [
                        'name' => __("Withdrawal time"),
                        'key' => 'withdrawal_time',
                        'desc' => __("T + 1 refers to applying for withdrawal, the money will arrive on the next day, T + 0 refers to applying for withdrawal, and the money will arrive on the same day. Under normal circumstances, when a member applies for withdrawal, it will be processed in about 10 minutes after approval to the wallet."),
                        'values' => $allVip->pluck('withdrawal_time')
                    ],
                    [
                        'name' => __("Network fee"),
                        'key' => 'network_fee',
                        'desc' => __("Every transaction in Ethereum needs to deduct network fees. Depending on the membership level, we will deduct some of the fees."),
                        'values' => $allVip->pluck('network_fee')
                    ],
                    [
                        'name' => __("Withdrawal verification"),
                        'key' => 'need_withdrawal_verification',
                        'desc' => __("In order to ensure that all members' activities are carried out in the Coinbase wallet, vip0 members need to verify the wallet balance when making withdrawal, and the maximum amount of payment = wallet balance."),
                        'values' => $allVip->pluck('need_withdrawal_verification')
                    ],
                ]],
        ];

        return [
            'user' => [
                'total_staking_usdc_amount' => $user->total_staking_amount,
                'next_level_need_stake' => $nextLevelNeedStake,
                'level_up' => $levelUp,
                'vips_id' => $vip->id,
            ],
            'levels' => Vips::select('id', 'name', 'need_stake')->get(),
            'vips' => $vips
        ];
    }

    /**
     * @return mixed
     */
    public function select(): mixed
    {
        return Vips::where('id', '>', 1)
            ->selectRaw('loan_charges as value, CONCAT_WS("","VIP",id-1) as label')
            ->get()
            ->toArray();
    }
}
