<?php

use App\Enums\FundsProductTypeEnum;
use App\Enums\FundsRiskTypeEnum;
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
        Schema::create('funds', function (Blueprint $table) {
            $table->id();
            $table->myEnum('product_type', FundsProductTypeEnum::class);
            $table->myEnum('risk_type', FundsRiskTypeEnum::class);
            $table->string('name', 50);
            $table->foreignId('main_coins_id')->comment('ref[Coins]');
            $table->foreignId('sub_coins_id')->comment('ref[Coins]')->nullable();
//            $table->foreignId('return_coins_id')->comment('ref[Coins]')->nullable();
//            $table->json('sparkline')->nullable();
            $table->json('profits')->nullable();
            $table->unsignedSmallInteger('duration')->default(0);
            $table->float8('apr_start');
            $table->float8('apr_end');
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
        Schema::dropIfExists('funds');
    }
};
