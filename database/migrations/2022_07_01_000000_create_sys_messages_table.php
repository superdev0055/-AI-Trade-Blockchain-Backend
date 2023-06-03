<?php

use App\Enums\SysMessageTypeEnum;
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
        Schema::create('sys_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('users_id');
            $table->string('type', 30)->comment(SysMessageTypeEnum::comment('类型'));
            $table->boolean('has_read')->default(0);
            $table->string('intro')->nullable();
            $table->json('content')->nullable();
            $table->amount('usdc');
            $table->amount('usd');
            $table->string('url')->nullable();
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
        Schema::dropIfExists('sys_messages');
    }
};
