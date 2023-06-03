<?php

use App\Enums\UserBonusesStatusEnum;
use App\Enums\UserBonusesTypeEnum;
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
        Schema::create('bonuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_users_id')->comment('ref[Users]')->nullable();
            $table->foreignId('to_users_id')->comment('ref[Users]');
            $table->myEnum('type', UserBonusesTypeEnum::class);
            $table->amount('friend_bonus');
            $table->float('bonus_rate')->default(0);
            $table->amount('bonus');
            $table->myEnum('status', UserBonusesStatusEnum::class, 'status', UserBonusesStatusEnum::Waiting->name);
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
        Schema::dropIfExists('bonuses');
    }
};
