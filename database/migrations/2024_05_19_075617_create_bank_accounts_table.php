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
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable()->index('banks_user_id_index');
            $table->unsignedInteger('admin_id')->nullable()->index('banks_admin_id_index');
            $table->unsignedInteger('currency_id')->nullable()->index('banks_currency_id_index');
            $table->unsignedInteger('country_id')->nullable()->index('banks_country_id_index');
            $table->unsignedInteger('file_id')->nullable()->index('banks_file_id_index');
            $table->string('bank_name', 191)->nullable();
            $table->string('bank_branch_name', 191)->nullable();
            $table->string('bank_branch_city', 191)->nullable();
            $table->string('bank_branch_address', 191)->nullable();
            $table->string('account_name', 191)->nullable();
            $table->string('account_number', 191)->nullable();
            $table->string('swift_code', 191)->nullable();
            $table->string('is_default', 3)->default('No')->comment('No, Yes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
