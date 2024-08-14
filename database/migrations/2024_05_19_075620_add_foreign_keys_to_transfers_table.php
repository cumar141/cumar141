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
        Schema::table('transfers', function (Blueprint $table) {
            $table->foreign(['bank_id'])->references(['id'])->on('bank_accounts')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['currency_id'])->references(['id'])->on('currencies')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['file_id'])->references(['id'])->on('files')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['receiver_id'])->references(['id'])->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['sender_id'])->references(['id'])->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transfers', function (Blueprint $table) {
            $table->dropForeign('transfers_bank_id_foreign');
            $table->dropForeign('transfers_currency_id_foreign');
            $table->dropForeign('transfers_file_id_foreign');
            $table->dropForeign('transfers_receiver_id_foreign');
            $table->dropForeign('transfers_sender_id_foreign');
        });
    }
};
