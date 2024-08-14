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
        Schema::create('crypto_providers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 30)->index('crypto_providers_name_idx');
            $table->string('alias', 30)->unique();
            $table->string('description', 191)->nullable();
            $table->string('logo', 91)->nullable();
            $table->text('subscription_details')->nullable();
            $table->string('status', 11)->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crypto_providers');
    }
};
