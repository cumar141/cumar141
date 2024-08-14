<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('withdrawal_details', function (Blueprint $table) {
            $table->foreign(['withdrawal_id'])->references(['id'])->on('withdrawals')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawal_details', function (Blueprint $table) {
            $table->dropForeign('withdrawal_details_withdrawal_id_foreign');
        });
    }
};
