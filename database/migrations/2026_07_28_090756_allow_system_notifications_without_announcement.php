<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // System-generated alerts (loan delinquency, etc.) aren't tied to an
        // Announcement, so that FK can no longer be required. MySQL allows
        // NULL in a FK column without dropping the constraint.
        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('announcement_id')->nullable()->change();
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('loan_id')->nullable()->after('announcement_id')->constrained('loans')->nullOnDelete();
        });

        DB::statement("ALTER TABLE notifications MODIFY type ENUM('announcement', 'meeting', 'reminder', 'resolution', 'loan_grace_interest', 'loan_penalty', 'loan_barangay_summon', 'loan_legal_action') NOT NULL DEFAULT 'announcement'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE notifications MODIFY type ENUM('announcement', 'meeting', 'reminder', 'resolution') NOT NULL DEFAULT 'announcement'");

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('loan_id');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('announcement_id')->nullable(false)->change();
        });
    }
};
