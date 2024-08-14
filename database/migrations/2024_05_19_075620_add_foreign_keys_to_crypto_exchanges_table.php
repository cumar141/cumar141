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
        Schema::table('crypto_exchanges', function (Blueprint $table) {
            $table->foreign(['from_currency'])->references(['id'])->on('currencies')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign(['to_currency'])->references(['id'])->on('currencies')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crypto_exchanges', function (Blueprint $table) {
            $table->dropForeign('crypto_exchanges_from_currency_foreign');
            $table->dropForeign('crypto_exchanges_to_currency_foreign');
            $table->dropForeign('crypto_exchanges_user_id_foreign');
        });
    }
};
