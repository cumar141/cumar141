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
        Schema::create('crypto_asset_api_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('payment_method_id')->index();
            $table->integer('object_id')->index('crypto_asset_api_logs_object_id_idx')->comment('wallet_id or cryto_sent_id or crypto_received_id');
            $table->string('object_type', 20)->index('crypto_asset_api_logs_object_type_idx');
            $table->string('network', 10)->index('crypto_asset_api_logs_network_idx')->comment('Networks/Cryto Curencies - BTC,LTC,DOGE');
            $table->text('payload')->comment('Crypto Api\'s Payloads (e.g - get_new_address(), get_balance(), withdraw(),etc.');
            $table->integer('confirmations')->default(0)->index('crypto_asset_api_logs_confirmations_idx');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crypto_asset_api_logs');
    }
};
