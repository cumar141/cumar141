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
        Schema::create('crypto_asset_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('currency_id')->unique();
            $table->unsignedInteger('crypto_provider_id')->index('crypto_asset_settings_crypto_provider_id_idx');
            $table->unsignedInteger('payment_method_id')->index('crypto_asset_settings_payment_method_id_idx');
            $table->string('network', 30)->unique()->comment('Networks/Cryto Curencies - BTC,LTC,DT etc.');
            $table->text('network_credentials');
            $table->string('status', 11)->default('Active')->comment('Active/Inactive');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crypto_asset_settings');
    }
};
