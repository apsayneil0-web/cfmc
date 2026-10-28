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
        // Groups multiple ScheduleRequest rows submitted together in one
        // "request several machines at once" form (e.g. Tractor + Seeder for
        // the same job). Each machine still gets its own row — own date,
        // own conflict/capacity checks, own approval — this just tags which
        // ones were submitted as a set. Null for ordinary single requests.
        Schema::table('schedule_requests', function (Blueprint $table) {
            $table->uuid('batch_id')->nullable()->after('id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule_requests', function (Blueprint $table) {
            $table->dropColumn('batch_id');
        });
    }
};
