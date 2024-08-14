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
        Schema::create('notification_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('type')->nullable()->unique('type');
            $table->string('key')->nullable();
            $table->text('value')->nullable();
            $table->string('status', 191)->default('Active');
            $table->timestamps();

            $table->unique(['type'], 'type_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_messages');
    }
};
