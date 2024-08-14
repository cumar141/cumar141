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
        Schema::create('currency_payment_methods', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('currency_id')->index();
            $table->unsignedInteger('method_id')->index();
            $table->text('activated_for')->nullable()->comment('transaction types like deposit, withdrawal, investment etc.');
            $table->string('method_data')->comment('input field\'s title and value like client_id, client_secret etc');
            $table->string('type', 100);
            $table->string('alias', 100);
            $table->tinyInteger('processing_time')->default(0)->comment('time in days');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_payment_methods');
    }
};
