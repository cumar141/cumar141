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
        Schema::create('dispute_discussions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('dispute_id')->nullable()->index();
            $table->integer('user_id')->index();
            $table->string('type', 6)->comment('Admin, User');
            $table->longText('message')->nullable();
            $table->string('file')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispute_discussions');
    }
};
