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
        Schema::create('farmer_crop', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained()->onDelete('cascade');
            $table->foreignId('crop_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['farmer_id', 'crop_id']);
        });

        // Carry each farmer's existing single crop over into the new pivot table.
        DB::table('farmers')->whereNotNull('crop_id')->get(['id', 'crop_id'])->each(function ($farmer) {
            DB::table('farmer_crop')->insert([
                'farmer_id' => $farmer->id,
                'crop_id' => $farmer->crop_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        Schema::table('farmers', function (Blueprint $table) {
            $table->dropForeign(['crop_id']);
            $table->dropColumn('crop_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('farmers', function (Blueprint $table) {
            $table->foreignId('crop_id')->nullable()->after('contact_number')->constrained('crops')->onDelete('cascade');
        });

        DB::table('farmer_crop')->orderBy('id')->get()->groupBy('farmer_id')->each(function ($rows, $farmerId) {
            DB::table('farmers')->where('id', $farmerId)->update(['crop_id' => $rows->first()->crop_id]);
        });

        Schema::dropIfExists('farmer_crop');
    }
};
