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
        Schema::create('ussd_sms_queue', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('session', 100);
            $table->string('sms_platform', 100);
            $table->string('recipient', 100);
            $table->string('body', 500);
            $table->boolean('being_processed')->default(false);
            $table->boolean('sent')->default(false);
            $table->tinyInteger('attempts');
            $table->timestamp('registered_at')->useCurrent();
            $table->timestamp('sent_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ussd_sms_queue');
    }
};
