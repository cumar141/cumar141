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
        Schema::create('partner_balance', function (Blueprint $table) {
            $table->integer('id', true);
            $table->tinyInteger('port');
            $table->string('partner', 100)->unique('partner');
            $table->string('type', 100);
            $table->decimal('balance', 10);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_balance');
    }
};
