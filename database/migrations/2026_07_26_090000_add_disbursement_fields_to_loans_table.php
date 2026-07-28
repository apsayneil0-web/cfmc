<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->decimal('remaining_balance', 12, 2)->nullable()->change();
            $table->date('next_due_date')->nullable()->change();

            $table->timestamp('disbursed_at')->nullable()->after('next_due_date');
            $table->enum('disbursement_method', ['cash', 'bank_transfer', 'check'])->nullable()->after('disbursed_at');
            $table->string('reference_no')->nullable()->after('disbursement_method');
            $table->foreignId('disbursed_by')->nullable()->after('reference_no')->constrained('users')->nullOnDelete();
        });

        DB::statement("ALTER TABLE loans MODIFY status ENUM('pending_disbursement', 'active', 'fully_paid', 'overdue', 'archived') NOT NULL DEFAULT 'pending_disbursement'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE loans MODIFY status ENUM('active', 'fully_paid', 'overdue', 'archived') NOT NULL DEFAULT 'active'");

        Schema::table('loans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('disbursed_by');
            $table->dropColumn(['disbursed_at', 'disbursement_method', 'reference_no']);

            $table->decimal('remaining_balance', 12, 2)->nullable(false)->change();
            $table->date('next_due_date')->nullable(false)->change();
        });
    }
};
