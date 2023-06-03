<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('membership_card')->default(false);
            $table->boolean('first_withdrawal_free')->default(false);
            $table->date('first_withdrawal_free_date')->nullable();
            $table->date('membership_start_date')->nullable();
            $table->date('membership_end_date')->nullable();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('membership_card');
            $table->dropColumn('first_withdrawal_free');
            $table->dropColumn('first_withdrawal_free_date');
            $table->dropColumn('membership_start_date');
            $table->dropColumn('membership_end_date');
        });
    }

};
