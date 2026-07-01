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
        Schema::table('farmers', function (Blueprint $table) {
            $table->string('full_name')->nullable();
            $table->string('barangay')->nullable();
            $table->string('contact_number')->nullable();
            $table->decimal('land_area', 10, 2)->nullable();
            $table->string('documents')->nullable();
            $table->enum('status', ['Pending Approval', 'Approved', 'Archived'])->default('Pending Approval');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('farmers', function (Blueprint $table) {
            $table->dropColumn(['full_name', 'barangay', 'contact_number', 'land_area', 'documents', 'status']);
        });
    }
};