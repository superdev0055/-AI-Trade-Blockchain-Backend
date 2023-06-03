<?php

use App\Enums\AssetLogsTypeEnum;
use App\Enums\AssetsTypeEnum;
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
        Schema::create('asset_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('users_id');
            $table->foreignId('assets_id');
            $table->myEnum('type', AssetsTypeEnum::class, '类别');
            $table->amount('before');
            $table->amount();
            $table->amount('after');
            $table->string('remark', 50)->nullable();
            $table->text('reason')->nullable();
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
        Schema::dropIfExists('asset_logs');
    }
};
