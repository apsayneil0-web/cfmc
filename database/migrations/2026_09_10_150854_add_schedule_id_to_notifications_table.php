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
        // Schedule-moved notifications had no link back to the schedule they
        // describe, so the bell's click-to-view-detail modal silently did
        // nothing for them (unlike loan/announcement notifications).
        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('schedule_id')->nullable()->after('loan_id')->constrained('schedule_requests')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('schedule_id');
        });
    }
};
