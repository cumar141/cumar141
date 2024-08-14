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
        Schema::create('merchant_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50)->unique();
            $table->string('description', 191)->nullable();
            $table->string('icon', 50);
            $table->decimal('fee', 20, 8)->nullable()->default(0);
            $table->string('fee_bearer', 10)->default('Merchant')->index('fee_bearer_merchant_groups_idx')->comment('Merchant, User');
            $table->string('is_default', 5)->default('No')->comment('No or Yes');
            $table->tinyInteger('is_active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchant_groups');
    }
};
