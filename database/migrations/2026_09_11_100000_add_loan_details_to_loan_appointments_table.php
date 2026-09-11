<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lets a farmer attach the loan they want to discuss to their
     * appointment request — a pre-request the Manager reviews and, if it
     * checks out, encodes into a real LoanRequest themselves via the
     * existing Loan Request form. Nullable throughout since existing
     * appointments (and a farmer who just wants to talk, no numbers yet)
     * won't have this filled in.
     */
    public function up(): void
    {
        Schema::table('loan_appointments', function (Blueprint $table) {
            $table->decimal('requested_amount', 12, 2)->nullable()->after('purpose');
            $table->string('loan_purpose')->nullable()->after('requested_amount');
            $table->unsignedSmallInteger('repayment_terms_months')->nullable()->after('loan_purpose');
            $table->string('collateral')->nullable()->after('repayment_terms_months');
            $table->string('documents_path')->nullable()->after('collateral');
        });
    }

    public function down(): void
    {
        Schema::table('loan_appointments', function (Blueprint $table) {
            $table->dropColumn(['requested_amount', 'loan_purpose', 'repayment_terms_months', 'collateral', 'documents_path']);
        });
    }
};
