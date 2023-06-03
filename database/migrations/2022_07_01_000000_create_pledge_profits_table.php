<?php

use App\Enums\PledgeProfitsDepositStatusEnum;
use App\Enums\PledgeProfitsExchangeStatusEnum;
use App\Enums\PledgeProfitsStakingTypeEnum;
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
        Schema::create('pledge_profits', function (Blueprint $table) {
            $table->id();

            // foreign
            $table->foreignId('pledges_id')->comment('所属pledge');
            $table->foreignId('users_id')->comment('所属用户');
            $table->foreignId('parent_1_id')->comment('所属父1级用户')->nullable();
            $table->foreignId('parent_2_id')->comment('所属父2级用户')->nullable();
            $table->foreignId('vips_id')->comment('用户vip等级');

            $table->boolean('is_trail')->comment('是否试用');
            $table->boolean('is_new_day')->comment('是否新的一天');

            // Automated trading
            $table->dateTime('datetime')->comment('时间');
            $table->unsignedTinyInteger('round')->comment('轮数');

            $table->amount('staking', '本金');
            $table->unsignedSmallInteger('duration')->comment('期限');
            $table->amount('lose_staking_amount', '损失的本金');

            $table->float8('apy', '真实利润/本金');
            $table->float8('loan_apy', '真实利润/本金*杠杆');
            $table->float8('actual_apy', '扣除费用利润/本金');
            $table->float8('actual_loan_apy', '扣除费用利润/本金*杠杆');

            $table->amount('income', '盈亏');
            $table->amount('actual_income', '最终盈亏');

            $table->amount('loyalty_fee', '进出贡献费用');
            $table->amount('loyalty_amount', '进出贡献金额');

            // Automated exchange
            $table->boolean('can_automatic_exchange')->comment('是否自动兑换');
            $table->dateTime('manual_exchanged_at')->comment('手动兑换时间')->nullable();
            $table->float('manual_exchange_fee_percent')->comment('手动兑换手续费比例')->default(0);
            $table->amount('manual_exchange_fee_amount', '手动兑换手续费金额');
            $table->json('funds_detail_json')->comment('兑换明细数据')->nullable();
            $table->myEnum('exchange_status', PledgeProfitsExchangeStatusEnum::class, '兑换状态', PledgeProfitsExchangeStatusEnum::Finished->name); //

            // Profit guarantee
            $table->boolean('can_profit_guarantee')->comment('是否开启利润保护');
            $table->float('minimum_guarantee_apy')->comment('最低保护apy');
            $table->amount('minimum_guarantee_amount', '最低保护金额');
            $table->amount('profit_guarantee_amount', '需保护金额');
            $table->boolean('done_profit_guarantee')->comment('是否完成了自动保护')->nullable();
            $table->amount('deposit_total_amount', '贡献不够时，需要补足的总金额');
            $table->amount('deposit_loyalty_amount', '从贡献补的金额');
            $table->amount('deposit_staking_amount', '从质押补的金额');
            $table->myEnum('deposit_status', PledgeProfitsDepositStatusEnum::class, '质押状态', null, true);
            $table->foreignId('deposit_web3_transactions_id')->comment('所属web3交易')->nullable();
            $table->dateTime('deposited_at')->comment('补足的时间')->nullable();

            // Leveraged investment
            // Automatic loan repayment
            $table->boolean('can_leveraged_investment')->comment('是否开启杠杆');
            $table->boolean('can_automatic_loan_repayment')->comment('是否开启自动还贷');
            $table->unsignedSmallInteger('leverage')->comment('杠杆');
            $table->amount('loan_amount', '贷款金额');
            $table->float('loan_charges')->comment('贷款费率');
            $table->amount('loan_charges_fee', '贷款费用');

            // Prevent liquidation
            $table->boolean('can_prevent_liquidation')->comment('是否开启爆仓防护');
            $table->amount('prevent_liquidation_amount', '爆仓防护金额');

            // E-mail notification
            $table->boolean('can_email_notification')->comment('是否打开email通知');

            // Automatic Airdrop Bonus
            $table->boolean('can_automatic_airdrop_bonus')->comment('是否自动空投');

            // Automatic staking
            $table->boolean('can_automatic_staking')->comment('是否自动质押');
            $table->myEnum('staking_type', PledgeProfitsStakingTypeEnum::class, '自动质押类型', null, true);

            // Automatic withdrawal
            $table->boolean('can_automatic_withdrawal')->comment('是否自动出款');
            $table->amount('automatic_withdrawal_amount', '自动出款金额');

            $table->amount('child_1_total_income_eth');
            $table->amount('child_2_total_income_eth');

            $table->timestamps();
            $table->index(['users_id', 'pledges_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pledge_profits');
    }
};
