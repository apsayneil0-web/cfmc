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
        Schema::table('schedule_requests', function (Blueprint $table) {
            $table->foreignId('crop_id')->nullable()->after('land_size')->constrained('crops')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('crop_id');
        });
    }
};
