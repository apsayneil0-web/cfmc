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
            // Distinct from `user_id` (the manager who registered the farmer):
            // this links the farmer's approved membership record to their own login account.
            $table->foreignId('account_user_id')->nullable()->unique()->after('user_id')->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('farmers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('account_user_id');
        });
    }
};
