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
        Schema::create('crypto_exchanges', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable()->index('crypto_exchagnes_user_id_idx');
            $table->unsignedInteger('from_currency')->index('crypto_exchagnes_from_currency_idx');
            $table->unsignedInteger('to_currency')->index('crypto_exchagnes_to_currency_idx');
            $table->string('uuid', 13);
            $table->decimal('exchange_rate', 20, 8)->default(0);
            $table->decimal('amount', 20, 8)->default(0);
            $table->decimal('get_amount', 20, 8)->nullable();
            $table->decimal('fee', 20, 8)->default(0);
            $table->string('receiver_address', 100)->nullable();
            $table->string('receiving_details', 191)->nullable();
            $table->string('verification_via', 20)->nullable();
            $table->string('email_phone', 50)->nullable();
            $table->string('file_name', 191)->nullable();
            $table->string('payment_details', 191)->nullable();
            $table->string('send_via', 20)->nullable();
            $table->string('receive_via', 20)->nullable();
            $table->string('type', 20);
            $table->string('status', 20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crypto_exchanges');
    }
};
