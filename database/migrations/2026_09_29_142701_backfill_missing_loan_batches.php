<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The original create_loan_batches_and_add_type_to_loan_requests_table
     * migration seeds Batch 1..10 as part of its up(), but that only runs
     * once, the moment the migration first applies. On an environment where
     * loan_batches ended up empty afterward (e.g. the table was restored
     * from a pre-seed backup, or cleared some other way), the Batch Loan
     * dropdown has nothing to list even though the schema itself is fine.
     * This backfills whatever's missing, keyed on the unique batch_number so
     * it's a no-op anywhere the 10 rows already exist (like local).
     */
    public function up(): void
    {
        for ($number = 1; $number <= 10; $number++) {
            $exists = DB::table('loan_batches')->where('batch_number', $number)->exists();

            if (! $exists) {
                DB::table('loan_batches')->insert([
                    'batch_number' => $number,
                    'capacity' => 10,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Backfill-only — never removes batches, since by the time this runs
        // they may already contain real loan requests.
    }
};
