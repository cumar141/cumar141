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
        Schema::create('user_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->unsignedInteger('country_id')->index();
            $table->boolean('email_verification')->default(false);
            $table->string('phone_verification_code', 6)->nullable();
            $table->string('two_step_verification_type', 21)->default('disabled')->comment('disabled, email, phone, phone_email, google_authenticator');
            $table->string('two_step_verification_code', 6)->nullable();
            $table->boolean('two_step_verification')->default(false);
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->string('city', 25)->nullable();
            $table->string('state', 25)->nullable();
            $table->text('address_1')->nullable();
            $table->text('address_2')->nullable();
            $table->string('default_currency', 10)->nullable();
            $table->string('timezone', 191)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_details');
    }
};
