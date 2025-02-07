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
        Schema::create('currencies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type', 20)->default('fiat')->comment('fiat or crypto');
            $table->string('name', 50);
            $table->char('symbol', 10);
            $table->string('code', 21);
            $table->decimal('rate', 20, 8)->default(0);
            $table->string('logo', 100)->nullable();
            $table->string('default', 3)->default('0');
            $table->string('exchange_from', 6)->default('local');
            $table->string('allowed_wallet_creation', 4)->default('No');
            $table->string('address', 191)->nullable();
            $table->string('status', 11)->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
