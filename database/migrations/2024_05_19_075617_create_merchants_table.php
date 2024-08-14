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
        Schema::create('merchants', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->unsignedInteger('currency_id')->nullable()->index();
            $table->unsignedInteger('merchant_group_id')->nullable()->index();
            $table->string('merchant_uuid', 13)->nullable()->comment('Unique ID for each Merchant');
            $table->string('business_name', 191);
            $table->string('site_url', 100);
            $table->string('type', 11)->comment('standard or express');
            $table->string('note');
            $table->string('logo', 100)->nullable();
            $table->decimal('fee', 20, 8)->nullable()->default(0);
            $table->string('status', 12)->default('Moderation')->comment('Moderation, Disapproved, Approved');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchants');
    }
};
