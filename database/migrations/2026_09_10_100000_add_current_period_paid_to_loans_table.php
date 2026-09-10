<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            // Running total of payments applied toward the CURRENT due
            // period only (partial payments accumulate here until they
            // cover monthly_due, at which point next_due_date advances and
            // this resets) — lets next_due_date auto-advance instead of
            // requiring a manager to edit it by hand after every payment.
            $table->decimal('current_period_paid', 12, 2)->default(0)->after('remaining_balance');
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('current_period_paid');
        });
    }
};
