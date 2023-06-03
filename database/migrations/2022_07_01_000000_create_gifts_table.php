<?php

use App\Enums\GiftStatusEnum;
use App\Enums\GiftTypeEnum;
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
        Schema::create('gifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('users_id');
            $table->string('code', 10)->comment('编号')->nullable();
            $table->amount();
            $table->myEnum('type', GiftTypeEnum::class, '类型', null, true);
            $table->unsignedSmallInteger('total_count')->comment('总数量');
            $table->unsignedSmallInteger('received_count')->comment('已领取数量')->default(0);
            $table->float('fee')->comment('手续费');
            $table->amount('fee_amount', '手续费');
            $table->myEnum('status', GiftStatusEnum::class, '状态', GiftStatusEnum::OnGoing->name);
            $table->json('formula')->comment('计算公式');
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
        Schema::dropIfExists('gifts');
    }
};
