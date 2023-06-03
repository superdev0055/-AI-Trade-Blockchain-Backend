<?php

use App\Enums\PledgesStatusEnum;
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
        Schema::create('pledges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('users_id');
            $table->boolean('is_trail');
            $table->dateTime('started_at');
            $table->dateTime('ended_at');
            $table->dateTime('canceled_at')->nullable();

            $table->amount('staking');
//            $table->unsignedSmallInteger('leverage');
            $table->float8('estimate_apy');
            $table->float8('actual_apy');
            $table->float8('actual_loan_apy');
//            $table->amount('loyalty_value_today');
//            $table->amount('total_loyalty_value');
            $table->amount('earnings_this_node');
            $table->amount('earnings_today');
            $table->unsignedTinyInteger('auto_joined_funds')->default(4);
//            $table->unsignedSmallInteger('duration');
//            $table->unsignedInteger('earnings_ranking')->nullable();
//            $table->amount('estimate_airdrop');
            $table->boolean('next_round_is_1')->default(false);
            $table->myEnum('status', PledgesStatusEnum::class);
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
        Schema::dropIfExists('pledges');
    }
};
