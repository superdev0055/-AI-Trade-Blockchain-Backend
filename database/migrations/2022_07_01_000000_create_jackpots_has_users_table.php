<?php

use App\Enums\AirdropStatusEnum;
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
        Schema::create('jackpots_has_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jackpots_id');
            $table->foreignId('users_id');
            $table->foreignId('web3_transactions_id')->nullable();
            $table->amount('loyalty');
            $table->amount('airdrop');
            $table->unsignedInteger('rank')->nullable();
            $table->dateTime('expired_at')->nullable();
            $table->boolean('can_automatic_airdrop_bonus')->default(0);
            $table->myEnum('status', AirdropStatusEnum::class, 'status', AirdropStatusEnum::NotReady->name);
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
        Schema::dropIfExists('jackpots_has_users');
    }
};
