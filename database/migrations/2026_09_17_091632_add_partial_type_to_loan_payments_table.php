<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE loan_payments MODIFY type ENUM('payment', 'prepayment', 'partial', 'interest') NOT NULL DEFAULT 'payment'");
    }

    public function down(): void
    {
        DB::statement("UPDATE loan_payments SET type = 'payment' WHERE type = 'partial'");
        DB::statement("ALTER TABLE loan_payments MODIFY type ENUM('payment', 'prepayment', 'interest') NOT NULL DEFAULT 'payment'");
    }
};
