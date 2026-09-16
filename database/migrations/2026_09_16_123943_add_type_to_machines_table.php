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
        // `name` used to double as both the type ("Seeder") and the specific
        // unit's label. Splitting them out: `type` groups units for the
        // picker/list, `name` stays the specific unit's own label (e.g.
        // "Seeder 1"). Existing rows are backfilled with type = their
        // current name, since that's what they were being used as.
        Schema::table('machines', function (Blueprint $table) {
            $table->string('type')->nullable()->after('name');
        });

        DB::table('machines')->update(['type' => DB::raw('name')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
