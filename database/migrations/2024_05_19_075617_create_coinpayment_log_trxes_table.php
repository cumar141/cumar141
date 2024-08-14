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
        Schema::create('coinpayment_log_trxes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->integer('merchant_id');
            $table->string('payment_id', 191);
            $table->string('payment_address', 191);
            $table->string('coin', 10);
            $table->string('fiat', 10);
            $table->string('status_text', 191);
            $table->integer('status')->default(0);
            $table->dateTime('payment_created_at');
            $table->dateTime('expired');
            $table->dateTime('confirmation_at')->nullable();
            $table->double('amount');
            $table->integer('confirms_needed');
            $table->string('qrcode_url', 191);
            $table->string('status_url', 191);
            $table->text('payload');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coinpayment_log_trxes');
    }
};
