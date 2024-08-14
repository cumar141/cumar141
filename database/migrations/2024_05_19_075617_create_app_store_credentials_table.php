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
        Schema::create('app_store_credentials', function (Blueprint $table) {
            $table->increments('id');
            $table->string('has_app_credentials', 3)->comment('Yes or No');
            $table->string('link', 191)->nullable();
            $table->string('logo', 100);
            $table->string('company', 6)->comment('Google or Apple');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_store_credentials');
    }
};
