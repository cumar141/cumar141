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
        Schema::table('merchant_payments', function (Blueprint $table) {
            $table->foreign(['currency_id'])->references(['id'])->on('currencies')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['merchant_id'])->references(['id'])->on('merchants')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['payment_method_id'])->references(['id'])->on('payment_methods')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merchant_payments', function (Blueprint $table) {
            $table->dropForeign('merchant_payments_currency_id_foreign');
            $table->dropForeign('merchant_payments_merchant_id_foreign');
            $table->dropForeign('merchant_payments_payment_method_id_foreign');
            $table->dropForeign('merchant_payments_user_id_foreign');
        });
    }
};
