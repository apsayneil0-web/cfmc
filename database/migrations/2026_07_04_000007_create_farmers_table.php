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
        Schema::create('farmers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('middle_initial', 5)->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable();
            $table->string('contact_number');
            $table->foreignId('crop_id')->constrained('crops')->onDelete('cascade');
            $table->decimal('land_area', 10, 2);
            $table->string('province');
            $table->string('municipality');
            $table->string('barangay')->nullable();
            $table->enum('status', ['pending', 'approved', 'archived'])->default('pending');
            $table->text('documents_path')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farmers');
    }
};