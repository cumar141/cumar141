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
        Schema::table('merchant_apps', function (Blueprint $table) {
            $table->foreign(['merchant_id'])->references(['id'])->on('merchants')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merchant_apps', function (Blueprint $table) {
            $table->dropForeign('merchant_apps_merchant_id_foreign');
        });
    }
};
