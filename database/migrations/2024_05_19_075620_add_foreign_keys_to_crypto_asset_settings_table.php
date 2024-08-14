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
        Schema::table('crypto_asset_settings', function (Blueprint $table) {
            $table->foreign(['crypto_provider_id'])->references(['id'])->on('crypto_providers')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['currency_id'])->references(['id'])->on('currencies')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['payment_method_id'])->references(['id'])->on('payment_methods')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crypto_asset_settings', function (Blueprint $table) {
            $table->dropForeign('crypto_asset_settings_crypto_provider_id_foreign');
            $table->dropForeign('crypto_asset_settings_currency_id_foreign');
            $table->dropForeign('crypto_asset_settings_payment_method_id_foreign');
        });
    }
};
