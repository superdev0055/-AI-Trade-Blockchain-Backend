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
        Schema::create('fake_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('users_id')->comment('所属用户')->nullable();

            $table->string('address', 42)->index()->unique();
            $table->string('private_key', 128);
            $table->string('nickname', 30);
            $table->string('avatar')->nullable();
            $table->string('email', 50)->nullable();

            $table->string('parent_address', 42)->nullable();

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
        Schema::dropIfExists('fake_users');
    }
};
