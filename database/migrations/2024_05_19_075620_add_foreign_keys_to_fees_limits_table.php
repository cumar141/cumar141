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
        Schema::table('fees_limits', function (Blueprint $table) {
            $table->foreign(['currency_id'])->references(['id'])->on('currencies')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['payment_method_id'])->references(['id'])->on('payment_methods')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['transaction_type_id'])->references(['id'])->on('transaction_types')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fees_limits', function (Blueprint $table) {
            $table->dropForeign('fees_limits_currency_id_foreign');
            $table->dropForeign('fees_limits_payment_method_id_foreign');
            $table->dropForeign('fees_limits_transaction_type_id_foreign');
        });
    }
};
