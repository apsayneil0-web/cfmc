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
        Schema::table('farmers', function (Blueprint $table) {
            // Full address fields (barangay already exists, adding municipality and province)
            $table->string('municipality')->nullable();
            $table->string('province')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('farmers', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'middle_initial', 'last_name', 'suffix', 'barangay', 'municipality', 'province']);
        });
    }
};