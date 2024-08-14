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
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreign(['bank_id'])->references(['id'])->on('bank_accounts')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['currency_id'])->references(['id'])->on('currencies')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['end_user_id'])->references(['id'])->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['file_id'])->references(['id'])->on('files')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['merchant_id'])->references(['id'])->on('merchants')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['payment_method_id'])->references(['id'])->on('payment_methods')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['transaction_type_id'])->references(['id'])->on('transaction_types')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign('transactions_bank_id_foreign');
            $table->dropForeign('transactions_currency_id_foreign');
            $table->dropForeign('transactions_end_user_id_foreign');
            $table->dropForeign('transactions_file_id_foreign');
            $table->dropForeign('transactions_merchant_id_foreign');
            $table->dropForeign('transactions_payment_method_id_foreign');
            $table->dropForeign('transactions_transaction_type_id_foreign');
            $table->dropForeign('transactions_user_id_foreign');
        });
    }
};
