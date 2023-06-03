<?php

use Hhiphopman168\LaravelDev\DevTools\Helpers\MySQLHelper;
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
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('username', 20);
            $table->string('password', 128);
            $table->ipAddress('last_login_ip')->nullable();
            $table->dateTime('last_login_time')->nullable();
            $table->unsignedTinyInteger('login_failed_count')->default(0);
            $table->dateTime('locked_util')->nullable();
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
        Schema::dropIfExists('admins');
    }
};
