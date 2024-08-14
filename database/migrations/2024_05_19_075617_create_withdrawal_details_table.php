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
        Schema::create('withdrawal_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('withdrawal_id')->nullable()->index();
            $table->integer('type')->comment('2=Paypal, 5=Bank, 8=Crypto');
            $table->string('email', 191)->nullable();
            $table->string('crypto_address', 191)->nullable();
            $table->string('account_name', 191)->nullable();
            $table->string('account_number', 191)->nullable();
            $table->string('bank_branch_name', 191)->nullable();
            $table->string('bank_branch_city', 191)->nullable();
            $table->string('bank_branch_address', 191)->nullable();
            $table->unsignedInteger('country')->nullable();
            $table->string('swift_code', 191)->nullable();
            $table->string('bank_name', 191)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawal_details');
    }
};
