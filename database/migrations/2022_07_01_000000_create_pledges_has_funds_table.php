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
        Schema::create('pledges_has_funds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('users_id');
            $table->foreignId('pledges_id');
            $table->foreignId('funds_id');
            $table->foreignId('main_coins_id')->comment('ref[Coins]');
            $table->foreignId('sub_coins_id')->comment('ref[Coins]')->nullable();
            $table->json('profits')->nullable();
            $table->amount('main_coin_price');

//            $table->float8('apy_start');
//            $table->float8('apy_end');
            $table->float8('apy_current');

//            $table->string('coin1_symbol', 20);
//            $table->string('coin1_icon');
//            $table->amount('coin1_price');
//            $table->string('coin2_symbol', 20)->nullable();
//            $table->string('coin2_icon')->nullable();
//            $table->amount('coin2_price');
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
        Schema::dropIfExists('pledges_has_funds');
    }
};
