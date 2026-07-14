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
            $table->text('certificate_of_title_path')->nullable()->after('documents_path');
            $table->text('barangay_certification_path')->nullable()->after('certificate_of_title_path');
            $table->text('rsbsa_path')->nullable()->after('barangay_certification_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('farmers', function (Blueprint $table) {
            $table->dropColumn(['certificate_of_title_path', 'barangay_certification_path', 'rsbsa_path']);
        });
    }
};
