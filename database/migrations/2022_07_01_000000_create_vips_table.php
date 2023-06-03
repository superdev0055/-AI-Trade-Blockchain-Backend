<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vips', function (Blueprint $table) {
            $table->id();
            $table->string('name', 10)->comment('名称');
            $table->amount('need_stake');

            $table->boolean('can_automatic_trade');
            $table->boolean('can_trail_bonus');
            $table->boolean('can_automatic_exchange');
            $table->boolean('can_email_notification');
            $table->boolean('can_leveraged_investment');
            $table->boolean('can_automatic_loan_repayment');
            $table->boolean('can_prevent_liquidation');
            $table->boolean('can_profit_guarantee');
            $table->boolean('can_automatic_airdrop_bonus');
            $table->boolean('can_automatic_staking');
            $table->boolean('can_automatic_withdrawal');

            $table->unsignedSmallInteger('daily_referral_rewards');
            $table->float8('level_1_refer');
            $table->float8('level_2_refer');
            $table->float8('level_3_refer');
            $table->boolean('can_pm_friends');
            $table->boolean('can_customize_online_status');
            $table->boolean('can_view_contact_details');
            $table->boolean('can_send_gift');

            $table->unsignedSmallInteger('leveraged_investment');
            $table->float8('loan_charges');
            $table->float8('minimum_apy_guarantee');
            $table->boolean('can_promotion_first_notice');
            $table->boolean('can_exclusive_customer_service');
            $table->unsignedSmallInteger('max_staking_term');

            $table->amount('minimum_withdrawal_limit');
            $table->amount('maximum_withdrawal_limit');
            $table->unsignedSmallInteger('number_of_withdrawals');
            $table->unsignedSmallInteger('withdrawal_time');
            $table->float('network_fee', 4, 2);
            $table->boolean('need_withdrawal_verification');

            // 已废弃
            $table->amount('max_help_withdraw_amount');

            // 每天能辅助提款验证的次数
            $table->unsignedSmallInteger('max_help_withdraw_count')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vips');
    }
};
