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
        Schema::create('crops', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // corn, rice, cassava
            $table->timestamps();
        });

        // Insert default crop types
        DB::table('crops')->insert([
            ['name' => 'Corn', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Rice', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cassava', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crops');
    }
};