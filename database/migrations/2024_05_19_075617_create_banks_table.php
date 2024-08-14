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
        Schema::create('banks', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('currency_id')->nullable()->index();
            $table->unsignedInteger('country_id')->nullable()->index();
            $table->string('bank_name', 191)->nullable();
            $table->string('bank_branch_name', 191)->nullable();
            $table->string('bank_branch_city', 191)->nullable();
            $table->string('bank_branch_address', 191)->nullable();
            $table->string('is_Active', 3)->default('No')->comment('No, Yes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};
