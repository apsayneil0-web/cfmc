<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            // false = a general-use machine (truck, hauling) that is
            // borrowed without a crop or land size.
            $table->boolean('requires_crop')->default(true)->after('daily_hectare_limit');
        });

        // Which crops a machine works with. A machine with no rows here
        // works with every crop, so existing machines keep showing for all.
        Schema::create('crop_machine', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('machine_id')->constrained()->cascadeOnDelete();
            $table->unique(['crop_id', 'machine_id']);
        });

        Schema::table('schedule_requests', function (Blueprint $table) {
            $table->string('purpose', 20)->default('crop')->after('machine_id');
            $table->decimal('land_size', 8, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('schedule_requests', function (Blueprint $table) {
            $table->dropColumn('purpose');
        });

        Schema::dropIfExists('crop_machine');

        Schema::table('machines', function (Blueprint $table) {
            $table->dropColumn('requires_crop');
        });
    }
};
