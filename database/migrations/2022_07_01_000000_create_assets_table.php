<?php

use App\Enums\AssetsPendingStatusEnum;
use App\Enums\AssetsPendingWithdrawalTypeEnum;
use App\Enums\AssetsTypeEnum;
use App\Enums\AssetsPendingTypeEnum;
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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('users_id');
            $table->myEnum('type', AssetsTypeEnum::class, '类别');
            $table->foreignId('coins_id');
            $table->string('symbol', 10);
            $table->string('icon');
            $table->amount('balance');

            // Staking
            $table->dateTime('staking_ended_at')->nullable();

            // WithdrawAble
            $table->amount('withdrawable_snapshot');

            // Pending
            $table->myEnum('pending_type', AssetsPendingTypeEnum::class, 'type', null, true);
            $table->amount('pending_fee');
            $table->amount('reward_loyalty_amount');
            $table->myEnum('pending_status', AssetsPendingStatusEnum::class, 'status', null, true);
            $table->myEnum('pending_withdrawal_type', AssetsPendingWithdrawalTypeEnum::class, 'pending_withdrawal_type', null, true);
            $table->json('pending_withdrawal_approve_users')->nullable();
            $table->foreignId('pending_withdrawal_approve_users_id')->comment('ref[Users]')->nullable();
            $table->foreignId('pledge_profits_id')->nullable();
            $table->foreignId('web3_transactions_id')->nullable();
            $table->string('message')->nullable();
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
        Schema::dropIfExists('assets');
    }
};
