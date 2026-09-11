<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Links an appointment's loan pre-request to the real LoanRequest the
     * Manager encodes from it, once they do — lets the UI show "already
     * submitted" instead of offering to submit the same pre-request twice.
     */
    public function up(): void
    {
        Schema::table('loan_appointments', function (Blueprint $table) {
            $table->foreignId('loan_request_id')->nullable()->after('documents_path')->constrained('loan_requests')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('loan_appointments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('loan_request_id');
        });
    }
};
