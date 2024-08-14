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
        Schema::create('disputes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('claimant_id')->nullable()->index();
            $table->unsignedInteger('defendant_id')->nullable()->index();
            $table->unsignedInteger('transaction_id')->nullable()->index();
            $table->unsignedInteger('reason_id')->nullable()->index();
            $table->string('title', 191)->nullable();
            $table->text('description')->nullable();
            $table->string('code', 45)->nullable();
            $table->string('status', 7)->default('Open')->comment('Open, Closed, Solved');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};
