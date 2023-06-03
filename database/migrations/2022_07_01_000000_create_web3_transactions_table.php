<?php

use App\Enums\Web3TransactionsStatusEnum;
use App\Enums\Web3TransactionsTypeEnum;
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
        Schema::create('web3_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('users_id')->nullable();
            $table->foreignId('coins_id')->nullable();
            $table->nullableMorphs('operator');

            $table->myEnum('type', Web3TransactionsTypeEnum::class);

            // 下单信息
            $table->string('coin_network', 5)->comment('数币网络');
            $table->string('coin_symbol', 20)->comment('数币');
            $table->address('coin_address', '合约地址', null, true);
            $table->amount("coin_amount", '数币金额');
            $table->amount('usd_price', '折合usd');

            $table->address('from_address', 'from地址');
            $table->address('to_address', 'to地址');

            // 交易数据
            $table->json('send_transaction')->comment('发起交易信息')->nullable();
            $table->string('hash')->comment('交易hash')->nullable();
            $table->string('block_number', 15)->nullable();

            // 回调信息
            $table->json('receipt')->comment('交易源数据')->nullable();
            $table->text('message')->comment('回傳訊息')->nullable();

            $table->myEnum('status', Web3TransactionsStatusEnum::class, 'status', Web3TransactionsStatusEnum::WAITING->name);

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
        Schema::dropIfExists('web3_transactions');
    }
};
