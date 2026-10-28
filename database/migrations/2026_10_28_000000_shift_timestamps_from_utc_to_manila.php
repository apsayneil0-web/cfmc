<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The app clock moved from UTC to Asia/Manila (config/app.php). Every
 * system-written timestamp so far was saved as UTC wall time, so shift them
 * +8 hours to read correctly under the new clock.
 *
 * Only machine-written instants are shifted. Calendar dates and clock times
 * people enter (scheduled_date, start_time, due dates, transaction_date,
 * appointment_date, ...) are already Philippine local values and stay as-is.
 *
 * Must run exactly once, together with the config change: running it without
 * the config change (or twice) leaves every time 8 hours off.
 */
return new class extends Migration
{
    private const OFFSET_HOURS = 8;

    private const COLUMNS = [
        'activity_logs' => ['created_at'],
        'announcements' => ['archived_at', 'created_at', 'updated_at'],
        'cbus' => ['created_at', 'updated_at'],
        'cbu_transactions' => ['created_at', 'updated_at'],
        'complaints' => ['viewed_at', 'created_at', 'updated_at'],
        'crops' => ['created_at', 'updated_at'],
        'expenses' => ['created_at', 'updated_at'],
        'failed_jobs' => ['failed_at'],
        'farmers' => ['created_at', 'updated_at'],
        'farmer_crop' => ['created_at', 'updated_at'],
        'harvest_payments' => ['created_at', 'updated_at'],
        'loans' => [
            'partial_penalty_applied_at', 'grace_penalty_applied_at', 'barangay_summon_at',
            'legal_action_at', 'disbursed_at', 'archived_at', 'created_at', 'updated_at',
        ],
        'loan_appointments' => ['created_at', 'updated_at'],
        'loan_batches' => ['created_at', 'updated_at'],
        'loan_payments' => ['created_at', 'updated_at'],
        'loan_requests' => ['archived_at', 'created_at', 'updated_at'],
        'machines' => ['archived_at', 'created_at', 'updated_at'],
        'notifications' => ['created_at'],
        'password_reset_tokens' => ['created_at'],
        'roles' => ['created_at', 'updated_at'],
        'schedule_requests' => ['archived_at', 'created_at', 'updated_at'],
        'staff' => ['created_at', 'updated_at'],
        'users' => ['email_verified_at', 'OTPexpriry', 'LockedUntil', 'created_at', 'updated_at'],
    ];

    public function up(): void
    {
        $this->shift('+');
    }

    public function down(): void
    {
        $this->shift('-');
    }

    private function shift(string $direction): void
    {
        // Do the arithmetic on a fixed-offset session so TIMESTAMP columns
        // can't hit a DST gap in whatever zone the server's MySQL runs in.
        $previousZone = DB::selectOne('SELECT @@session.time_zone AS tz')->tz;
        DB::statement("SET time_zone = '+00:00'");

        try {
            DB::transaction(function () use ($direction) {
                foreach (self::COLUMNS as $table => $columns) {
                    if (! Schema::hasTable($table)) {
                        continue;
                    }

                    $columns = array_values(array_filter($columns, fn ($c) => Schema::hasColumn($table, $c)));
                    if (! $columns) {
                        continue;
                    }

                    // One UPDATE per table, every column set explicitly, so no
                    // column can auto-refresh to "now" while another is shifted.
                    $sets = implode(', ', array_map(
                        fn ($c) => "`{$c}` = `{$c}` {$direction} INTERVAL ".self::OFFSET_HOURS.' HOUR',
                        $columns
                    ));

                    DB::statement("UPDATE `{$table}` SET {$sets}");
                }
            });
        } finally {
            DB::statement('SET time_zone = ?', [$previousZone]);
        }
    }
};
