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
        Schema::create('countries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('short_name', 5)->unique();
            $table->string('name', 100);
            $table->string('iso3', 10)->nullable();
            $table->string('number_code', 10)->nullable();
            $table->string('phone_code', 10);
            $table->string('is_default', 6)->default('no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
