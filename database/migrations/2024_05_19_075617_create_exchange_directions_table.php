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
        Schema::create('exchange_directions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('from_currency_id')->index('exchange_directions_from_currency_id_idx');
            $table->unsignedInteger('to_currency_id')->index('exchange_directions_to_currency_id_idx');
            $table->string('type', 100);
            $table->string('exchange_from', 30)->default('local');
            $table->decimal('exchange_rate', 20, 8)->nullable();
            $table->decimal('fees_percentage', 20, 8)->default(0);
            $table->decimal('fees_fixed', 20, 8)->default(0);
            $table->decimal('min_amount', 20, 8)->default(0);
            $table->decimal('max_amount', 20, 8)->default(0);
            $table->text('payment_instruction')->nullable();
            $table->text('gateways')->nullable();
            $table->string('status', 11)->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_directions');
    }
};
