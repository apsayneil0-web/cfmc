<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cooperative's cut of a farmer's reported harvest income — 9% for
        // members, 12% for non-members. farmer_id is set for members;
        // farmer_name is a manual entry for non-members (same split as
        // schedule_requests' member_type/farmer_name).
        Schema::create('harvest_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->nullable()->constrained('farmers')->nullOnDelete();
            $table->string('farmer_name')->nullable();
            $table->enum('member_type', ['member', 'non-member']);
            $table->decimal('harvest_amount', 12, 2);
            $table->decimal('rate', 5, 2);
            $table->decimal('payment_amount', 12, 2);
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harvest_payments');
    }
};
