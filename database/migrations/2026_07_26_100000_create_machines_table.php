<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('serial_number')->nullable()->unique();
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('usage_hours')->default(0);
            $table->enum('status', ['available', 'in_use', 'maintenance'])->default('available');
            $table->string('assigned_operator')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machines');
    }
};
