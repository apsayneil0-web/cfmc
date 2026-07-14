<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row per loan application, before approval/finalization.
        Schema::create('loan_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained('farmers')->onDelete('cascade');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('requested_amount', 12, 2);
            $table->string('purpose');
            $table->unsignedSmallInteger('repayment_terms_months');
            $table->string('collateral')->nullable();
            $table->string('documents_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'denied', 'archived'])->default('pending');
            $table->text('denial_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });

        // One row per finalized/active loan. 1:1 with its originating (approved) request.
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_request_id')->unique()->constrained('loan_requests')->onDelete('cascade');
            $table->decimal('principal_amount', 12, 2);
            $table->decimal('interest_rate', 5, 2)->default(2.00);
            $table->unsignedSmallInteger('repayment_terms_months');
            $table->decimal('installment_amount', 12, 2);
            $table->string('collateral')->nullable();
            $table->decimal('remaining_balance', 12, 2);
            $table->date('next_due_date');
            $table->enum('status', ['active', 'fully_paid', 'overdue', 'archived'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });

        // Ledger of financial events (farmer payments and system-applied interest) against a loan.
        Schema::create('loan_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->onDelete('cascade');
            $table->enum('type', ['payment', 'interest'])->default('payment');
            $table->decimal('amount', 12, 2);
            $table->date('transaction_date');
            $table->decimal('balance_after', 12, 2);
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_payments');
        Schema::dropIfExists('loans');
        Schema::dropIfExists('loan_requests');
    }
};
