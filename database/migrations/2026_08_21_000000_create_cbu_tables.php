<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One CBU (Capital Build-Up) account per farmer, tracking their running balance.
        Schema::create('cbus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->unique()->constrained('farmers')->onDelete('cascade');
            $table->decimal('balance', 12, 2)->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // Ledger of contributions (increase balance) and expenses (decrease balance) against a CBU account.
        Schema::create('cbu_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cbu_id')->constrained('cbus')->onDelete('cascade');
            $table->enum('type', ['contribution', 'expense'])->default('contribution');
            $table->string('category')->nullable();
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
        Schema::dropIfExists('cbu_transactions');
        Schema::dropIfExists('cbus');
    }
};
