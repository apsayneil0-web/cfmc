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
            $table->date('scheduled_disbursement_date')->nullable()->after('next_due_date');
            $table->foreignId('scheduled_by')->nullable()->after('scheduled_disbursement_date')->constrained('users')->nullOnDelete();
        });

        // Batch Loan Management drops its manual "Mark Disbursed" action in
        // this release — any batch loan already awaiting disbursement needs a
        // scheduled_disbursement_date backfilled to today so the new
        // loans:process-scheduled-disbursements command picks it up on its
        // next run instead of getting stuck with no way to disburse it.
        DB::table('loans')
            ->join('loan_requests', 'loan_requests.id', '=', 'loans.loan_request_id')
            ->where('loans.status', 'pending_disbursement')
            ->where('loan_requests.type', 'batch')
            ->update(['loans.scheduled_disbursement_date' => now()->toDateString()]);
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('scheduled_by');
            $table->dropColumn('scheduled_disbursement_date');
        });
    }
};
