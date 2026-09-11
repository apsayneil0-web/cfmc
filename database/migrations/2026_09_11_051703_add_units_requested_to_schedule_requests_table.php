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
        // How many units of `machinery` this one request needs working in
        // parallel (e.g. 3 seeders on 3 hectares to finish faster), capped
        // at the Machine record's `quantity` when validated.
        Schema::table('schedule_requests', function (Blueprint $table) {
            $table->unsignedTinyInteger('units_requested')->default(1)->after('land_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule_requests', function (Blueprint $table) {
            $table->dropColumn('units_requested');
        });
    }
};
