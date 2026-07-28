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
        Schema::table('loans', function (Blueprint $table) {
            // Fixed anchor for the grace-period/delinquency clock, set once at
            // disbursement. Unlike next_due_date, this never advances, so
            // "N months since the due date" always means the same date.
            $table->date('original_due_date')->nullable()->after('next_due_date');

            // One-time policy events; each nullable timestamp doubles as the
            // "already applied" guard so re-running the policy check is safe.
            $table->timestamp('partial_penalty_applied_at')->nullable()->after('original_due_date');
            $table->timestamp('grace_penalty_applied_at')->nullable()->after('partial_penalty_applied_at');
            $table->timestamp('barangay_summon_at')->nullable()->after('grace_penalty_applied_at');
            $table->timestamp('legal_action_at')->nullable()->after('barangay_summon_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn([
                'original_due_date',
                'partial_penalty_applied_at',
                'grace_penalty_applied_at',
                'barangay_summon_at',
                'legal_action_at',
            ]);
        });
    }
};
