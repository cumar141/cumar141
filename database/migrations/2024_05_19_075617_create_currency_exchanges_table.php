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
        Schema::create('currency_exchanges', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->unsignedInteger('from_wallet')->nullable()->index();
            $table->unsignedInteger('to_wallet')->nullable()->index();
            $table->unsignedInteger('currency_id')->nullable()->index();
            $table->string('uuid', 13)->nullable()->comment('Unique ID (For Each Exchange)');
            $table->decimal('exchange_rate', 20, 8)->nullable()->default(0);
            $table->decimal('amount', 20, 8)->nullable()->default(0);
            $table->decimal('fee', 20, 8)->nullable()->default(0);
            $table->string('type', 6)->comment('In, Out');
            $table->string('status', 11)->comment('Pending, Success, Refund, Blocked');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_exchanges');
    }
};
