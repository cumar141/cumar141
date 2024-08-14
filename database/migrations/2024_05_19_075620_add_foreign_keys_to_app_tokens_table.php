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
        Schema::table('app_tokens', function (Blueprint $table) {
            $table->foreign(['app_id'])->references(['id'])->on('merchant_apps')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_tokens', function (Blueprint $table) {
            $table->dropForeign('app_tokens_app_id_foreign');
        });
    }
};
