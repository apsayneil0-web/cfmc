<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cooperative operating expenses: operational costs, machinery
        // expenditures, and replaceable parts, tracked separately from
        // farmer-facing loan/CBU ledgers.
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->enum('category', ['operational', 'machinery', 'replaceable_parts'])->default('operational');
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');
            $table->enum('status', ['pending', 'paid'])->default('paid');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
