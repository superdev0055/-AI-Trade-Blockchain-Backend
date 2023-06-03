<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('users')->delete();
        
        \DB::table('users')->insert(array (
            0 => 
            array (
                'id' => 1,
                'platform' => 'ERC20',
                'address' => '0x6034Ad1244b398d583ac3C4C4291be10Ac831C41',
                'invite_code' => 'KO3Y79PY',
                'vips_id' => 1,
                'referral_url' => NULL,
                'parent_1_id' => NULL,
                'parent_2_id' => NULL,
                'parent_3_id' => NULL,
                'email' => NULL,
                'email_verified_at' => NULL,
                'avatar' => NULL,
                'nickname' => NULL,
                'bio' => NULL,
                'phone_number' => NULL,
                'facebook' => NULL,
                'telegram' => NULL,
                'wechat' => NULL,
                'skype' => NULL,
                'whatsapp' => NULL,
                'line' => NULL,
                'zalo' => NULL,
                'profile_verified_at' => NULL,
                'profile_status' => 'Default',
                'full_name' => NULL,
                'id_no' => NULL,
                'country' => NULL,
                'city' => NULL,
                'id_front_img' => NULL,
                'id_reverse_img' => NULL,
                'identity_verified_at' => NULL,
                'identity_status' => 'Default',
                'can_automatic_trade' => 1,
                'can_trail_bonus' => 0,
                'can_automatic_exchange' => 1,
                'can_email_notification' => 0,
                'can_leveraged_investment' => 0,
                'can_automatic_loan_repayment' => 0,
                'can_prevent_liquidation' => 0,
                'can_profit_guarantee' => 0,
                'can_automatic_airdrop_bonus' => 1,
                'can_automatic_staking' => 0,
                'can_automatic_withdrawal' => 0,
                'total_balance' => '0.000000',
                'total_rate' => '0.000000',
                'total_staking_amount' => '0.000000',
                'total_withdraw_amount' => '0.000000',
                'total_income' => '0.000000',
                'total_actual_income' => '0.000000',
                'total_loyalty_value' => '0.000000',
                'total_today_loyalty_value' => '0.000000',
                'referral_count' => 0,
                'first_staking_time' => NULL,
                'leverage' => 1,
                'duration' => 7,
                'show_card_at' => '2023-02-18 09:03:05',
                'trailed_at' => NULL,
                'status' => 'Enable',
                'created_at' => '2023-02-18 09:03:02',
                'updated_at' => '2023-02-18 09:03:05',
            ),
        ));
        
        
    }
}