<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            $table->string('unavailable_reason_type')->nullable()->after('unavailable_reason');
            $table->decimal('unavailable_estimated_cost', 10, 2)->nullable()->after('unavailable_reason_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            $table->dropColumn(['unavailable_reason_type', 'unavailable_estimated_cost']);
        });
    }
};
