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
        Schema::table('dispute_discussions', function (Blueprint $table) {
            $table->foreign(['dispute_id'])->references(['id'])->on('disputes')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dispute_discussions', function (Blueprint $table) {
            $table->dropForeign('dispute_discussions_dispute_id_foreign');
        });
    }
};
