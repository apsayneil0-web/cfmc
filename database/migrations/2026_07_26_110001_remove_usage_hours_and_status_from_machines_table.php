<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Usage hours and status are no longer manually entered — both are now
     * computed live from completed/active bookings on the Machine model.
     */
    public function up(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            $table->dropColumn(['usage_hours', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            $table->unsignedInteger('usage_hours')->default(0)->after('quantity');
            $table->enum('status', ['available', 'in_use', 'maintenance'])->default('available')->after('usage_hours');
        });
    }
};
