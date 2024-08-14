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
        Schema::create('payout_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->integer('type');
            $table->string('email', 191)->nullable();
            $table->integer('currency_id')->nullable();
            $table->string('crypto_address', 191)->nullable();
            $table->string('account_name', 191)->nullable();
            $table->string('account_number', 191)->nullable();
            $table->string('bank_branch_name', 191)->nullable();
            $table->string('bank_branch_city', 191)->nullable();
            $table->string('bank_branch_address', 191)->nullable();
            $table->unsignedInteger('country')->nullable();
            $table->string('swift_code', 191)->nullable();
            $table->string('bank_name', 191)->nullable();
            $table->tinyInteger('default_payout')->default(0)->comment('0=not default, 1=default');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payout_settings');
    }
};
