<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedule_requests', function (Blueprint $table) {
            $table->foreignId('machine_id')->nullable()->after('machinery')
                ->constrained('machines')->nullOnDelete();
        });

        // Best-effort backfill: link existing bookings to a machine of the
        // same name, where one currently exists in the new inventory.
        DB::statement(<<<'SQL'
            UPDATE schedule_requests sr
            JOIN machines m ON m.name = sr.machinery AND m.archived_at IS NULL
            SET sr.machine_id = m.id
            WHERE sr.machine_id IS NULL
        SQL);
    }

    public function down(): void
    {
        Schema::table('schedule_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('machine_id');
        });
    }
};
