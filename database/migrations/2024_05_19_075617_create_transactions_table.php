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
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->unsignedInteger('end_user_id')->nullable()->index();
            $table->unsignedInteger('currency_id')->nullable()->index();
            $table->unsignedInteger('payment_method_id')->nullable()->index();
            $table->unsignedInteger('merchant_id')->nullable()->index();
            $table->unsignedInteger('bank_id')->nullable()->index();
            $table->unsignedInteger('file_id')->nullable()->index();
            $table->string('uuid', 13)->nullable()->comment('Unique ID');
            $table->string('refund_reference', 13)->nullable()->comment('Refund Reference');
            $table->integer('transaction_reference_id')->default(0);
            $table->string('reference_number', 30)->nullable();
            $table->string('external_reference', 30)->nullable();
            $table->unsignedInteger('transaction_type_id')->nullable()->index('transactions_transaction_type_id_foreign');
            $table->string('user_type', 15)->default('registered')->comment('registered, unregistered');
            $table->string('email', 191)->nullable();
            $table->string('phone', 20)->nullable();
            $table->decimal('subtotal', 20, 8)->nullable()->default(0);
            $table->decimal('percentage', 20, 8)->nullable()->default(0);
            $table->decimal('charge_percentage', 20, 8)->nullable()->default(0);
            $table->decimal('charge_fixed', 20, 8)->nullable()->default(0);
            $table->decimal('total', 20, 8)->nullable()->default(0);
            $table->decimal('balance', 20, 8)->nullable();
            $table->text('note')->nullable();
            $table->string('payment_status', 11)->nullable()->comment('Pending, Success, Blocked');
            $table->string('status', 11)->comment('Pending, Success, Refund, Blocked');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
