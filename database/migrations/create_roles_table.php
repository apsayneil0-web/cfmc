<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role', function (Blueprint $table) {
            $table->id('RoleID');
            $table->string('RoleName', 50)->unique();
            $table->index('RoleName', 'idx_RoleName');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role');
    }
};