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
        Schema::create('payment_verifications', function (Blueprint $table) {
            $table->integer('id', true)->unique('id');
            $table->string('platform', 500);
            $table->string('transaction_id', 500);
            $table->string('uuid', 500)->primary();
            $table->string('reference_id', 500)->nullable();
            $table->tinyInteger('being_processed')->default(0);
            $table->tinyText('status')->default('PENDING');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->default('0000-00-00 00:00:00');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->default('0000-00-00 00:00:00');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_verifications');
    }
};
