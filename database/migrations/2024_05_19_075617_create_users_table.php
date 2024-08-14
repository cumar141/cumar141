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
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('role_id')->nullable()->index();
            $table->integer('branch_id')->default(0)->index('users_branch_index');
            $table->string('type', 30)->default('user')->comment('user or merchant');
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('formattedPhone', 50)->nullable();
            $table->string('phone', 50)->nullable()->unique();
            $table->string('phone1', 50)->nullable();
            $table->string('phone2', 50)->nullable();
            $table->string('phone3', 50)->nullable();
            $table->text('google2fa_secret')->nullable();
            $table->string('defaultCountry', 4)->nullable();
            $table->string('carrierCode', 6)->nullable();
            $table->string('email', 191)->nullable()->unique();
            $table->string('password', 191);
            $table->string('phrase', 191)->nullable();
            $table->boolean('address_verified')->default(false);
            $table->boolean('identity_verified')->default(false);
            $table->string('status', 11)->default('Active')->comment('Active, Inactive, Suspended');
            $table->rememberToken();
            $table->string('fcm_token')->nullable();
            $table->timestamps();
            $table->string('picture', 100);
            $table->tinyInteger('login_hit_count')->default(0);
            $table->boolean('is_temp_blocked')->default(false);
            $table->timestamp('temp_block_time')->nullable();
            $table->tinyInteger('access_web')->default(0);
            $table->tinyInteger('biometric_login')->default(0);
            $table->string('teller_uuid', 13);
            $table->softDeletes();

            $table->unique(['phone1', 'phone2', 'phone3'], 'phone1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
