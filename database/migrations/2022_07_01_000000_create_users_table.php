<?php

use App\Enums\CoinNetworkEnum;
use App\Enums\PledgeProfitsStakingTypeEnum;
use App\Enums\UsersIdentityStatusEnum;
use App\Enums\UsersProfileStatusEnum;
use App\Enums\UsersStatusEnum;
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
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // base
            $table->enum('platform', CoinNetworkEnum::columns())->default(CoinNetworkEnum::ERC20->name);
            $table->string('address', 42)->index()->unique();
            $table->string('invite_code', 64)->nullable();
            $table->foreignId('vips_id')->default(1);

            // referrals
            $table->string('referral_url', 200)->nullable();
            $table->foreignId('parent_1_id')->nullable();
            $table->foreignId('parent_2_id')->nullable();
            $table->foreignId('parent_3_id')->nullable();

            // verify email
            $table->string('email', 50)->nullable();
            $table->dateTime('email_verified_at')->nullable();

            // profile
            $table->string('avatar')->nullable();
            $table->string('nickname', 30)->nullable();
            $table->text('bio')->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->string('facebook', 50)->nullable();
            $table->string('telegram', 50)->nullable();
            $table->string('wechat', 50)->nullable();
            $table->string('skype', 50)->nullable();
            $table->string('whatsapp', 50)->nullable();
            $table->string('line', 50)->nullable();
            $table->string('zalo', 50)->nullable();
            $table->dateTime('profile_verified_at')->nullable();
            $table->myEnum('profile_status', UsersProfileStatusEnum::class);
            $table->string('profile_error_message')->nullable();
            $table->dateTime('profile_error_last_at')->nullable();
            $table->unsignedSmallInteger('profile_error_count_today')->default(0);

            // identity
            $table->string('full_name', 50)->nullable();
            $table->string('id_no', 50)->nullable();
            $table->string('country', 50)->nullable();
            $table->string('city', 50)->nullable();
            $table->string('id_front_img')->nullable();
            $table->string('id_reverse_img')->nullable();
            $table->string('self_photo_img')->nullable();
            $table->dateTime('identity_verified_at')->nullable();
            $table->myEnum('identity_status', UsersIdentityStatusEnum::class);
            $table->string('identity_error_message')->nullable();
            $table->dateTime('identity_error_last_at')->nullable();
            $table->unsignedSmallInteger('identity_error_count_today')->default(0);

            // ai trade settings
            $table->boolean('can_automatic_trade')->default(1);
            $table->boolean('can_trail_bonus')->default(0);
            $table->boolean('can_automatic_exchange')->default(1);
            $table->boolean('can_email_notification')->default(0);
            $table->boolean('can_leveraged_investment')->default(0);
            $table->boolean('can_automatic_loan_repayment')->default(0);
            $table->boolean('can_prevent_liquidation')->default(0);
            $table->amount('prevent_liquidation_amount');
            $table->boolean('can_profit_guarantee')->default(0);
            $table->boolean('can_automatic_airdrop_bonus')->default(0);
            $table->boolean('can_automatic_staking')->default(0);
            $table->myEnum('staking_type', PledgeProfitsStakingTypeEnum::class, 'staking type', null, true); // 类型
            $table->boolean('can_automatic_withdrawal')->default(0);
            $table->amount('automatic_withdrawal_amount');

            $table->boolean('can_say')->default(1);

            // 资产数据
            $table->amount('staking');
            $table->amount('withdrawable');

            // 统计数据
            $table->amount('total_balance'); // 跑任务：Assets中的 staking + withdrawables
            $table->amount('total_rate'); // 跑任务：total_income / total_balance

            $table->amount('total_staking_amount'); // 触发：有staking的时候
            $table->amount('total_withdraw_amount');    // 触发：有withdraw的时候

            $table->amount('total_income'); // 跑任务：compute pledge
            $table->amount('total_actual_income'); // 跑任务：compute pledge
            $table->amount('total_loyalty_value'); // 跑任务：compute pledge
            $table->amount('total_today_loyalty_value'); // 跑任务：compute pledge
//            $table->amount('current_loyalty_value'); // 跑任务：compute pledge
//            $table->amount('current_today_loyalty_value'); // 跑任务：compute pledge

            $table->unsignedInteger('referral_count')->default(0);

            $table->dateTime('first_staking_time')->nullable();

            // pledge setting
            $table->unsignedSmallInteger('leverage')->default(1);
            $table->unsignedSmallInteger('duration')->default(7);

            # fake
            $table->boolean('is_cool_user')->default(false);

            # 当日辅助了多少次
            $table->unsignedInteger('today_had_help_count')->default(0);

            // status
            $table->dateTime('show_card_at')->nullable();
            $table->dateTime('trailed_at')->nullable();
            $table->myEnum('status', UsersStatusEnum::class, 'status', UsersStatusEnum::Enable->name);

            $table->dateTime('last_login_at')->nullable(); // 最后一次登录时间

            // 代理后台
            $table->string('username', 50)->nullable();
            $table->string('password', 100)->nullable();

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
        Schema::dropIfExists('users');
    }
};
