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
        Schema::table('exchange_directions', function (Blueprint $table) {
            $table->foreign(['from_currency_id'])->references(['id'])->on('currencies')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign(['to_currency_id'])->references(['id'])->on('currencies')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exchange_directions', function (Blueprint $table) {
            $table->dropForeign('exchange_directions_from_currency_id_foreign');
            $table->dropForeign('exchange_directions_to_currency_id_foreign');
        });
    }
};
