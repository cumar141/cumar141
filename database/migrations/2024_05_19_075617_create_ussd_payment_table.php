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
        Schema::create('ussd_payment', function (Blueprint $table) {
            $table->string('session', 100)->primary();
            $table->string('reference', 100)->nullable();
            $table->string('trx_reference', 500)->nullable();
            $table->string('receipt', 100)->nullable();
            $table->string('sender', 100);
            $table->string('receiver', 100);
            $table->decimal('cleared_amount', 16);
            $table->decimal('amount', 16);
            $table->decimal('rate', 16);
            $table->decimal('fee', 16);
            $table->string('platform', 100);
            $table->string('payment_method', 100);
            $table->string('partner', 100);
            $table->text('misc')->nullable();
            $table->boolean('being_processed')->default(false);
            $table->tinyInteger('status')->default(0)->comment('0: Waiting Sender Payment
1: Payment Received from Sender
2: Payment Sent to Receiver
3: Payment to Receiver Failed
4: Payment needs Human
5: Payment Blocked');
            $table->tinyInteger('attempts')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('sent_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ussd_payment');
    }
};
