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
        DB::statement("ALTER TABLE farmers MODIFY COLUMN status ENUM('pending', 'approved', 'archived', 'rejected') DEFAULT 'pending'");

        Schema::table('farmers', function (Blueprint $table) {
            $table->text('rejection_reason')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('farmers', function (Blueprint $table) {
            $table->dropColumn('rejection_reason');
        });

        DB::statement("ALTER TABLE farmers MODIFY COLUMN status ENUM('pending', 'approved', 'archived') DEFAULT 'pending'");
    }
};
