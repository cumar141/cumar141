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
        Schema::create('ussd_session', function (Blueprint $table) {
            $table->string('session', 100)->primary();
            $table->string('phone', 100);
            $table->tinyInteger('identity_verified')->default(0);
            $table->tinyInteger('authenticated')->default(0);
            $table->tinyInteger('shortcut')->default(0);
            $table->tinyInteger('dialog')->default(0);
            $table->string('dialog_type', 100)->nullable();
            $table->string('stage', 100)->default('CHECK_USER');
            $table->string('prompt', 100)->nullable();
            $table->longText('input')->nullable()->default('[]');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ussd_session');
    }
};
