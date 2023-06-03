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
        Schema::create('configs', function (Blueprint $table) {
            $table->id();
            $table->json('trail')->nullable();
            $table->json('trail_kill')->nullable();
            $table->json('user_kill')->nullable();
            $table->json('vip_kill')->nullable();
            $table->json('address')->nullable();
            $table->json('gift')->nullable();
            $table->json('profit')->nullable();
            $table->json('fee')->nullable();
            $table->json('other')->nullable();
            $table->json('staking_reward_loyalty')->nullable();
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
        Schema::dropIfExists('configs');
    }
};
