<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Legacy Admin/Manager accounts created before the Staff table existed have
     * no name on file yet; relax these so a profile-picture-only Staff row can
     * be created for them without also requiring a name.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE staff MODIFY first_name VARCHAR(255) NULL');
        DB::statement('ALTER TABLE staff MODIFY last_name VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE staff MODIFY first_name VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE staff MODIFY last_name VARCHAR(255) NOT NULL');
    }
};
