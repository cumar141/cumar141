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
        Schema::create('merchant_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('merchant_id')->nullable()->index();
            $table->unsignedInteger('currency_id')->nullable()->index();
            $table->unsignedInteger('payment_method_id')->nullable()->index();
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->string('gateway_reference', 50)->nullable();
            $table->string('order_no', 50)->nullable();
            $table->string('item_name', 150)->nullable();
            $table->string('uuid', 13)->nullable();
            $table->string('fee_bearer', 10)->comment('Merchant, User');
            $table->decimal('percentage', 20, 8)->nullable()->default(0);
            $table->decimal('charge_percentage', 20, 8)->nullable()->default(0);
            $table->decimal('charge_fixed', 20, 8)->nullable()->default(0);
            $table->decimal('amount', 20, 8)->nullable()->default(0);
            $table->decimal('total', 20, 8)->nullable()->default(0);
            $table->string('status', 11)->default('Success')->comment('Pending, Success, Refund, Blocked');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchant_payments');
    }
};
