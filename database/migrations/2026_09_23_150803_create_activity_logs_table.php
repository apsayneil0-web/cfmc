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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            // Nullable: a small number of events (e.g. a failed login for a
            // username that doesn't exist) have no authenticated user yet.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Short machine key, e.g. "loan.approved", "user.locked".
            $table->string('action', 100)->index();

            // The human-readable sentence shown in the log table.
            $table->string('description');

            // What the action was about (a Farmer, a LoanRequest, a User...).
            $table->nullableMorphs('subject');

            // Optional extra context (e.g. old/new status), never sensitive values.
            $table->json('properties')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamp('created_at')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
