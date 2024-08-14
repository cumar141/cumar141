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
        Schema::table('disputes', function (Blueprint $table) {
            $table->foreign(['claimant_id'])->references(['id'])->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['defendant_id'])->references(['id'])->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['reason_id'])->references(['id'])->on('reasons')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['transaction_id'])->references(['id'])->on('transactions')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('disputes', function (Blueprint $table) {
            $table->dropForeign('disputes_claimant_id_foreign');
            $table->dropForeign('disputes_defendant_id_foreign');
            $table->dropForeign('disputes_reason_id_foreign');
            $table->dropForeign('disputes_transaction_id_foreign');
        });
    }
};
