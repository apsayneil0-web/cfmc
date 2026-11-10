<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Proof of payment (receipt photo / deposit slip) attached when a CBU
     * contribution is recorded. Nullable: entries recorded before this
     * existed — and CBU expenses — have no proof on file.
     */
    public function up(): void
    {
        Schema::table('cbu_transactions', function (Blueprint $table) {
            $table->string('proof_document')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('cbu_transactions', function (Blueprint $table) {
            $table->dropColumn('proof_document');
        });
    }
};
