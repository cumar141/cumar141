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
        Schema::create('email_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('language_id')->nullable()->index('email_templates_language_id_idx');
            $table->string('name', 191)->index('email_templates_name_idx');
            $table->string('alias', 191)->index('email_templates_alias_idx');
            $table->string('subject', 191)->nullable();
            $table->text('body')->nullable();
            $table->string('lang', 2);
            $table->string('type', 5)->comment('email or sms');
            $table->string('status', 10)->default('Active')->comment('Active/Inactive');
            $table->string('group', 40);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
