<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            // Shortfall rolled forward from a "partial" payment that closed
            // out its period early — added on top of monthly_due for the
            // next period until it's paid off. See Loan::getAmountDueAttribute().
            $table->decimal('carried_over_amount', 12, 2)->default(0)->after('current_period_paid');
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('carried_over_amount');
        });
    }
};
