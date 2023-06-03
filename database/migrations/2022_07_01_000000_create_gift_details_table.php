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
        Schema::create('gift_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gifts_id');
            $table->foreignId('from_users_id')->comment('ref[Users]');
            $table->foreignId('to_users_id')->comment('ref[Users]');
            $table->amount();
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
        Schema::dropIfExists('gift_details');
    }
};
