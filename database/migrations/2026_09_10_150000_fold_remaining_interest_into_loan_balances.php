<?php

use App\Models\Loan;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * remaining_balance used to track principal only; going forward
     * Loan::disburse() sets it to principal + total flat interest for the
     * full term, so "Current Balance" and "Total Repayable" are the same
     * figure everywhere in the app instead of two numbers that can drift
     * apart. This backfills that same math onto loans already disbursed
     * under the old scheme, so existing loans show the bigger, correct
     * balance too instead of only loans disbursed after this migration.
     */
    public function up(): void
    {
        Loan::whereIn('status', ['active', 'overdue'])
            ->whereNotNull('remaining_balance')
            ->each(function (Loan $loan) {
                $installmentAmount = (float) $loan->installment_amount;

                if ($installmentAmount <= 0) {
                    return;
                }

                $interestPortion = round($loan->monthly_due - $installmentAmount, 2);
                $remainingTerms = max(0, (int) ceil((float) $loan->remaining_balance / $installmentAmount));

                $loan->update([
                    'remaining_balance' => round((float) $loan->remaining_balance + ($interestPortion * $remainingTerms), 2),
                ]);
            });
    }

    /**
     * Approximate reversal only — the forward math isn't cleanly invertible
     * (ceil() loses information), so this re-derives an estimated term count
     * from the folded-in balance rather than recovering the exact original.
     */
    public function down(): void
    {
        Loan::whereIn('status', ['active', 'overdue'])
            ->whereNotNull('remaining_balance')
            ->each(function (Loan $loan) {
                $installmentAmount = (float) $loan->installment_amount;

                if ($installmentAmount <= 0) {
                    return;
                }

                $interestPortion = round($loan->monthly_due - $installmentAmount, 2);
                $approxTerms = max(0, (int) ceil((float) $loan->remaining_balance / $loan->monthly_due));

                $loan->update([
                    'remaining_balance' => round(max(0, (float) $loan->remaining_balance - ($interestPortion * $approxTerms)), 2),
                ]);
            });
    }
};
