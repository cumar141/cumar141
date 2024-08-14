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
        Schema::create('topups', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable()->index('withdrawals_user_id_index');
            $table->unsignedInteger('currency_id')->nullable()->index('withdrawals_currency_id_index');
            $table->unsignedInteger('payment_method_id')->nullable()->index('withdrawals_payment_method_id_index');
            $table->string('uuid', 13)->nullable()->comment('Unique ID (For Each Withdrawal)');
            $table->decimal('charge_percentage', 20, 8)->nullable()->default(0);
            $table->decimal('charge_fixed', 20, 8)->nullable()->default(0);
            $table->decimal('subtotal', 20, 8)->nullable()->default(0);
            $table->decimal('amount', 20, 8)->nullable()->default(0);
            $table->string('payment_method_info');
            $table->string('status', 11)->comment('Pending, Success, Refund, Blocked');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topups');
    }
};
