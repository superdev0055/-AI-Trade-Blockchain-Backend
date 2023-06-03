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
        Schema::create('jackpot_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('users_id');
            $table->foreignId('jackpots_id');
            $table->foreignId('pledges_id');
            $table->foreignId('pledge_profits_id');
            $table->amount('before');
            $table->amount();
            $table->amount('after');
            $table->string('remark')->nullable();
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
        Schema::dropIfExists('jackpot_logs');
    }
};
