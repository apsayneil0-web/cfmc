<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fixed set of 10 batches (Batch 1..Batch 10) that batch loan requests
        // group into. One-to-many from here to loan_requests — no pivot needed.
        Schema::create('loan_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('batch_number')->unique();
            $table->unsignedTinyInteger('capacity')->default(10);
            $table->timestamps();
        });

        for ($number = 1; $number <= 10; $number++) {
            DB::table('loan_batches')->insert([
                'batch_number' => $number,
                'capacity' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('loan_requests', function (Blueprint $table) {
            $table->enum('type', ['regular', 'batch'])->default('regular')->after('farmer_id');
            $table->foreignId('batch_id')->nullable()->after('type')->constrained('loan_batches')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('batch_id');
            $table->dropColumn('type');
        });

        Schema::dropIfExists('loan_batches');
    }
};
