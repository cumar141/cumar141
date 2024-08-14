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
        Schema::create('fees_limits', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('currency_id')->nullable()->index();
            $table->unsignedInteger('transaction_type_id')->nullable()->index('fees_limits_transaction_type_id_foreign');
            $table->unsignedInteger('payment_method_id')->nullable()->index('fees_limits_payment_method_id_foreign');
            $table->decimal('charge_percentage', 20, 8)->default(0);
            $table->decimal('charge_fixed', 20, 8)->default(0);
            $table->decimal('min_limit', 20, 8)->default(1);
            $table->decimal('max_limit', 20, 8)->nullable();
            $table->string('processing_time', 4)->nullable()->default('0')->comment('time in days');
            $table->string('has_transaction', 3)->comment('Yes or No');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fees_limits');
    }
};
