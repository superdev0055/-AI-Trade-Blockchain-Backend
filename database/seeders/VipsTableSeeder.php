<?php

namespace Database\Seeders;

use App\Models\Vips;
use App\NewServices\VipsServices;
use Illuminate\Database\Seeder;

class VipsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        VipsServices::cleanVipCache();

        Vips::create([
            'name' => 'vip0', # 名称
            'need_stake' => 0, #

            'can_automatic_trade' => true, #
            'can_trail_bonus' => true, #
            'can_automatic_exchange' => true, #
            'can_email_notification' => true, #
            'can_leveraged_investment' => false, #
            'can_automatic_loan_repayment' => false, #
            'can_prevent_liquidation' => false, #
            'can_profit_guarantee' => false, #
            'can_automatic_airdrop_bonus' => false, #
            'can_automatic_staking' => false, #
            'can_automatic_withdrawal' => false, #

            'daily_referral_rewards' => 3, #
            'level_1_refer' => .03, #
            'level_2_refer' => .02, #
            'level_3_refer' => .01, #
            'can_pm_friends' => false, #
            'can_customize_online_status' => false, #
            'can_view_contact_details' => false, #
            'can_send_gift' => false, #

            'leveraged_investment' => 1, #
            'loan_charges' => 1, #
            'minimum_apy_guarantee' => 0, #
            'can_promotion_first_notice' => false, #
            'can_exclusive_customer_service' => false, #
            'max_staking_term' => 7, #

            'minimum_withdrawal_limit' => 30, #
            'maximum_withdrawal_limit' => 1000, #
            'number_of_withdrawals' => 1, #
            'withdrawal_time' => 1, #
            'network_fee' => 1, #
            'need_withdrawal_verification' => true, #
        ]);

        Vips::create([
            'name' => 'vip1', # 名称
            'need_stake' => 1000, #

            'can_automatic_trade' => true, #
            'can_trail_bonus' => true, #
            'can_automatic_exchange' => true, #
            'can_email_notification' => true, #
            'can_leveraged_investment' => true, #
            'can_automatic_loan_repayment' => true, #
            'can_prevent_liquidation' => true, #
            'can_profit_guarantee' => true, #
            'can_automatic_airdrop_bonus' => true, #
            'can_automatic_staking' => false, #
            'can_automatic_withdrawal' => false, #

            'daily_referral_rewards' => 5, #
            'level_1_refer' => .05, #
            'level_2_refer' => .03, #
            'level_3_refer' => .02, #
            'can_pm_friends' => false, #
            'can_customize_online_status' => false, #
            'can_view_contact_details' => false, #
            'can_send_gift' => false, #

            'leveraged_investment' => 20, #
            'loan_charges' => .3, #
            'minimum_apy_guarantee' => .05, #
            'can_promotion_first_notice' => false, #
            'can_exclusive_customer_service' => false, #
            'max_staking_term' => 15, #

            'minimum_withdrawal_limit' => 30, #
            'maximum_withdrawal_limit' => 10000, #
            'number_of_withdrawals' => 0, #
            'withdrawal_time' => 1, #
            'network_fee' => 1, #
            'need_withdrawal_verification' => false, #
        ]);

        Vips::create([
            'name' => 'vip2', # 名称
            'need_stake' => 5000, #

            'can_automatic_trade' => true, #
            'can_trail_bonus' => true, #
            'can_automatic_exchange' => true, #
            'can_email_notification' => true, #
            'can_leveraged_investment' => true, #
            'can_automatic_loan_repayment' => true, #
            'can_prevent_liquidation' => true, #
            'can_profit_guarantee' => true, #
            'can_automatic_airdrop_bonus' => true, #
            'can_automatic_staking' => true, #
            'can_automatic_withdrawal' => false, #

            'daily_referral_rewards' => 10, #
            'level_1_refer' => .05, #
            'level_2_refer' => .03, #
            'level_3_refer' => .02, #
            'can_pm_friends' => true, #
            'can_customize_online_status' => true, #
            'can_view_contact_details' => false, #
            'can_send_gift' => false, #

            'leveraged_investment' => 40, #
            'loan_charges' => .25, #
            'minimum_apy_guarantee' => .06, #
            'can_promotion_first_notice' => false, #
            'can_exclusive_customer_service' => false, #
            'max_staking_term' => 30, #

            'minimum_withdrawal_limit' => 30, #
            'maximum_withdrawal_limit' => 50000, #
            'number_of_withdrawals' => 0, #
            'withdrawal_time' => 0, #
            'network_fee' => .8, #
            'need_withdrawal_verification' => false, #
        ]);

        Vips::create([
            'name' => 'vip3', # 名称
            'need_stake' => 10000, #

            'can_automatic_trade' => true, #
            'can_trail_bonus' => true, #
            'can_automatic_exchange' => true, #
            'can_email_notification' => true, #
            'can_leveraged_investment' => true, #
            'can_automatic_loan_repayment' => true, #
            'can_prevent_liquidation' => true, #
            'can_profit_guarantee' => true, #
            'can_automatic_airdrop_bonus' => true, #
            'can_automatic_staking' => true, #
            'can_automatic_withdrawal' => true, #

            'daily_referral_rewards' => 20, #
            'level_1_refer' => .1, #
            'level_2_refer' => .06, #
            'level_3_refer' => .04, #
            'can_pm_friends' => true, #
            'can_customize_online_status' => true, #
            'can_view_contact_details' => true, #
            'can_send_gift' => true, #

            'leveraged_investment' => 60, #
            'loan_charges' => .2, #
            'minimum_apy_guarantee' => .07, #
            'can_promotion_first_notice' => true, #
            'can_exclusive_customer_service' => true, #
            'max_staking_term' => 60, #

            'minimum_withdrawal_limit' => 30, #
            'maximum_withdrawal_limit' => 100000, #
            'number_of_withdrawals' => 0, #
            'withdrawal_time' => 0, #
            'network_fee' => .6, #
            'need_withdrawal_verification' => false, #
        ]);

        Vips::create([
            'name' => 'vip4', # 名称
            'need_stake' => 50000, #

            'can_automatic_trade' => true, #
            'can_trail_bonus' => true, #
            'can_automatic_exchange' => true, #
            'can_email_notification' => true, #
            'can_leveraged_investment' => true, #
            'can_automatic_loan_repayment' => true, #
            'can_prevent_liquidation' => true, #
            'can_profit_guarantee' => true, #
            'can_automatic_airdrop_bonus' => true, #
            'can_automatic_staking' => true, #
            'can_automatic_withdrawal' => true, #

            'daily_referral_rewards' => 50, #
            'level_1_refer' => .1, #
            'level_2_refer' => .06, #
            'level_3_refer' => .04, #
            'can_pm_friends' => true, #
            'can_customize_online_status' => true, #
            'can_view_contact_details' => true, #
            'can_send_gift' => true, #

            'leveraged_investment' => 80, #
            'loan_charges' => .15, #
            'minimum_apy_guarantee' => .08, #
            'can_promotion_first_notice' => true, #
            'can_exclusive_customer_service' => true, #
            'max_staking_term' => 90, #

            'minimum_withdrawal_limit' => 30, #
            'maximum_withdrawal_limit' => 500000, #
            'number_of_withdrawals' => 0, #
            'withdrawal_time' => 0, #
            'network_fee' => .4, #
            'need_withdrawal_verification' => false, #
        ]);

        Vips::create([
            'name' => 'vip5', # 名称
            'need_stake' => 100000, #

            'can_automatic_trade' => true, #
            'can_trail_bonus' => true, #
            'can_automatic_exchange' => true, #
            'can_email_notification' => true, #
            'can_leveraged_investment' => true, #
            'can_automatic_loan_repayment' => true, #
            'can_prevent_liquidation' => true, #
            'can_profit_guarantee' => true, #
            'can_automatic_airdrop_bonus' => true, #
            'can_automatic_staking' => true, #
            'can_automatic_withdrawal' => true, #

            'daily_referral_rewards' => 100, #
            'level_1_refer' => .15, #
            'level_2_refer' => .1, #
            'level_3_refer' => .05, #
            'can_pm_friends' => true, #
            'can_customize_online_status' => true, #
            'can_view_contact_details' => true, #
            'can_send_gift' => true, #

            'leveraged_investment' => 100, #
            'loan_charges' => .1, #
            'minimum_apy_guarantee' => .09, #
            'can_promotion_first_notice' => true, #
            'can_exclusive_customer_service' => true, #
            'max_staking_term' => 180, #

            'minimum_withdrawal_limit' => 30, #
            'maximum_withdrawal_limit' => 1000000, #
            'number_of_withdrawals' => 0, #
            'withdrawal_time' => 0, #
            'network_fee' => .2, #
            'need_withdrawal_verification' => false, #
        ]);

        Vips::create([
            'name' => 'vip6', # 名称
            'need_stake' => 500000, #

            'can_automatic_trade' => true, #
            'can_trail_bonus' => true, #
            'can_automatic_exchange' => true, #
            'can_email_notification' => true, #
            'can_leveraged_investment' => true, #
            'can_automatic_loan_repayment' => true, #
            'can_prevent_liquidation' => true, #
            'can_profit_guarantee' => true, #
            'can_automatic_airdrop_bonus' => true, #
            'can_automatic_staking' => true, #
            'can_automatic_withdrawal' => true, #

            'daily_referral_rewards' => 200, #
            'level_1_refer' => .15, #
            'level_2_refer' => .1, #
            'level_3_refer' => .05, #
            'can_pm_friends' => true, #
            'can_customize_online_status' => true, #
            'can_view_contact_details' => true, #
            'can_send_gift' => true, #

            'leveraged_investment' => 125, #
            'loan_charges' => 0, #
            'minimum_apy_guarantee' => .1, #
            'can_promotion_first_notice' => true, #
            'can_exclusive_customer_service' => true, #
            'max_staking_term' => 360, #

            'minimum_withdrawal_limit' => 30, #
            'maximum_withdrawal_limit' => 50000000, #
            'number_of_withdrawals' => 0, #
            'withdrawal_time' => 0, #
            'network_fee' => 0, #
            'need_withdrawal_verification' => false, #
        ]);
    }
}
