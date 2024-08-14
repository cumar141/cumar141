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
        Schema::create('app_transactions_infos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('app_id')->index();
            $table->string('payment_method', 20);
            $table->decimal('amount', 20, 8);
            $table->string('currency', 10);
            $table->string('success_url', 191);
            $table->string('cancel_url', 191);
            $table->string('grant_id', 100);
            $table->string('token', 191);
            $table->string('expires_in', 100);
            $table->string('status', 11)->default('pending')->comment('pending, success, cancel');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_transactions_infos');
    }
};
