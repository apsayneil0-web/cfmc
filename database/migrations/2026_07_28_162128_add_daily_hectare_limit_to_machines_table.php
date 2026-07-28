<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            // Per-machine daily capacity, since different machine types realistically
            // cover different hectarage per day. Defaults to the cooperative's
            // standard 6-hectare policy but can be tuned per machine.
            $table->decimal('daily_hectare_limit', 6, 2)->default(6.00)->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            $table->dropColumn('daily_hectare_limit');
        });
    }
};
