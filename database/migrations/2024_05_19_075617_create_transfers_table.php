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
        Schema::create('transfers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('sender_id')->nullable()->index();
            $table->unsignedInteger('receiver_id')->nullable()->index();
            $table->unsignedInteger('currency_id')->nullable()->index();
            $table->unsignedInteger('bank_id')->nullable()->index();
            $table->unsignedInteger('file_id')->nullable()->index();
            $table->string('uuid', 13)->nullable()->comment('Unique ID (For Each Transfer)');
            $table->decimal('fee', 20, 8)->nullable()->default(0);
            $table->decimal('amount', 20, 8)->nullable()->default(0);
            $table->text('note')->nullable();
            $table->string('email', 191)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('status', 11)->comment('Pending, Success, Refund, Blocked');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
