<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * notifications.type was a fixed ENUM that had to be manually widened
     * every time a new notification type was introduced elsewhere in the
     * code — it was already out of sync with more than a dozen types
     * actually in use (Notification::typeDisplay() lists ~20; the ENUM
     * only allowed 8), causing "Data truncated for column 'type'" errors
     * whenever one of the missing ones was inserted (most recently
     * loan_appointment_booked, when a farmer books an appointment).
     * Converting to a plain string removes this whole class of bug going
     * forward — typeDisplay() already has a sensible default fallback for
     * any value it doesn't recognize, so nothing else needs to change.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE notifications MODIFY type VARCHAR(50) NOT NULL DEFAULT 'announcement'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE notifications MODIFY type ENUM('announcement', 'meeting', 'reminder', 'resolution', 'loan_grace_interest', 'loan_penalty', 'loan_barangay_summon', 'loan_legal_action') NOT NULL DEFAULT 'announcement'");
    }
};
