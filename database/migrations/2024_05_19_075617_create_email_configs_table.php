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
        Schema::create('email_configs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email_protocol', 191);
            $table->string('email_encryption', 191);
            $table->string('smtp_host', 191);
            $table->string('smtp_port', 191);
            $table->string('smtp_email', 191);
            $table->string('smtp_username', 191);
            $table->string('smtp_password', 191);
            $table->string('from_address', 191);
            $table->string('from_name', 191);
            $table->tinyInteger('status')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_configs');
    }
};
