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
        Schema::table('files', function (Blueprint $table) {
            $table->foreign(['admin_id'])->references(['id'])->on('admins')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['ticket_id'])->references(['id'])->on('tickets')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['ticket_reply_id'])->references(['id'])->on('ticket_replies')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropForeign('files_admin_id_foreign');
            $table->dropForeign('files_ticket_id_foreign');
            $table->dropForeign('files_ticket_reply_id_foreign');
            $table->dropForeign('files_user_id_foreign');
        });
    }
};
