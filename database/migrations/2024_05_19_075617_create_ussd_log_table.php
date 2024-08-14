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
        Schema::create('ussd_log', function (Blueprint $table) {
            $table->integer('id', true);
            $table->longText('metadata')->nullable();
            $table->string('session', 100);
            $table->string('phone', 100);
            $table->string('response', 500)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ussd_log');
    }
};
