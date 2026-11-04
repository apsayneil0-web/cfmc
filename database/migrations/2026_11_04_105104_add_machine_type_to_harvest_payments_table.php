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
        // Only a Harvester's payment is a percentage of reported harvest
        // value; other machine types use a flat rate (see
        // HarvestPayment::FIXED_RATES), so harvest_amount/rate no longer
        // apply to every row.
        Schema::table('harvest_payments', function (Blueprint $table) {
            $table->string('machine_type')->nullable()->after('member_type');
        });

        Schema::table('harvest_payments', function (Blueprint $table) {
            $table->decimal('harvest_amount', 12, 2)->nullable()->change();
            $table->decimal('rate', 5, 2)->nullable()->change();
        });

        // Backfill: every existing row was in fact computed with the
        // percentage method, so this accurately labels how it was already
        // calculated — it doesn't change any stored amount.
        DB::table('harvest_payments')->whereNull('machine_type')->update(['machine_type' => 'Harvester']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('harvest_payments', function (Blueprint $table) {
            $table->decimal('harvest_amount', 12, 2)->nullable(false)->change();
            $table->decimal('rate', 5, 2)->nullable(false)->change();
        });

        Schema::table('harvest_payments', function (Blueprint $table) {
            $table->dropColumn('machine_type');
        });
    }
};
