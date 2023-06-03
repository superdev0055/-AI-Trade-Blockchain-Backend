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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->date('day')->index();

            $table->amount('staking_amount');
            $table->amount('withdraw_amount');
            $table->amount('exchange_airdrop_amount');
            $table->amount('deposit_staking_amount');
            $table->amount('staking_reward_loyalty_amount');
            $table->amount('income_amount');
            $table->amount('actual_income_amount');
            $table->amount('withdrawable_amount');

            $table->unsignedInteger('user_register_count');
            $table->unsignedInteger('user_login_count');
            $table->unsignedInteger('trail_count');

            $table->unsignedInteger('staking_count');
            $table->unsignedInteger('withdraw_count');
            $table->unsignedInteger('exchange_airdrop_count');
            $table->unsignedInteger('deposit_staking_count');
            $table->unsignedInteger('staking_reward_loyalty_count');
            $table->unsignedInteger('income_count');
            $table->unsignedInteger('actual_income_count');
            $table->unsignedInteger('withdrawable_count');

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
        Schema::dropIfExists('reports');
    }
};
