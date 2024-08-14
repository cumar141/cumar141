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
        Schema::table('currency_exchanges', function (Blueprint $table) {
            $table->foreign(['currency_id'])->references(['id'])->on('currencies')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['from_wallet'])->references(['id'])->on('wallets')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['to_wallet'])->references(['id'])->on('wallets')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('currency_exchanges', function (Blueprint $table) {
            $table->dropForeign('currency_exchanges_currency_id_foreign');
            $table->dropForeign('currency_exchanges_from_wallet_foreign');
            $table->dropForeign('currency_exchanges_to_wallet_foreign');
            $table->dropForeign('currency_exchanges_user_id_foreign');
        });
    }
};
