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
        // varchar(160) is too narrow for generated messages (e.g. the
        // "Move Schedule +1 Day" reminder), which truncates a raw insert
        // and throws SQLSTATE[22001] instead of silently cutting the text.
        Schema::table('notifications', function (Blueprint $table) {
            $table->text('message')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('message', 160)->change();
        });
    }
};
