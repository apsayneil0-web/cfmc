<?php

namespace App\Console\Commands;

use App\Models\Loan;
use App\Models\LoanRequest;
use Illuminate\Console\Command;

/**
 * Non-destructive detection only — lists farmers who ended up with more than
 * one loan "in process" (a pre-existing data-integrity check now that the
 * one-loan-at-a-time rule is enforced going forward; existing duplicates
 * from before that fix still need a human to decide which one to archive).
 */
class FindDuplicateLoans extends Command
{
    protected $signature = 'loans:find-duplicates';

    protected $description = 'List farmers with more than one loan in process, for manual review — does not change any data';

    public function handle(): int
    {
        $foundAny = false;

        if ($this->reportDuplicateLoans()) {
            $foundAny = true;
        }

        $this->newLine();

        if ($this->reportDuplicateRequests()) {
            $foundAny = true;
        }

        if (! $foundAny) {
            $this->info('No duplicates found — every farmer has at most one loan in process.');
        }

        return self::SUCCESS;
    }

    /**
     * Farmers with more than one Loan in pending_disbursement/active/overdue
     * — the end state the one-loan-at-a-time rule exists to prevent.
     */
    private function reportDuplicateLoans(): bool
    {
        $loans = Loan::whereIn('status', ['pending_disbursement', 'active', 'overdue'])
            ->whereNull('archived_at')
            ->with('loanRequest.farmer')
            ->get()
            ->groupBy(fn (Loan $loan) => $loan->loanRequest->farmer_id)
            ->filter(fn ($group) => $group->count() > 1);

        if ($loans->isEmpty()) {
            $this->info('No farmers have more than one unpaid Loan.');

            return false;
        }

        $this->warn("Farmers with more than one unpaid Loan ({$loans->count()}):");

        $rows = $loans->flatMap(function ($group) {
            $farmer = $group->first()->loanRequest->farmer;

            return $group->map(fn (Loan $loan) => [
                $farmer?->full_name ?? "Farmer #{$group->first()->loanRequest->farmer_id}",
                'LN-'.str_pad((string) $loan->id, 3, '0', STR_PAD_LEFT),
                $loan->status,
                peso($loan->principal_amount),
                $loan->created_at->format('M d, Y'),
            ]);
        });

        $this->table(['Farmer', 'Loan ID', 'Status', 'Principal', 'Created'], $rows);

        return true;
    }

    /**
     * Farmers with more than one non-archived LoanRequest still pending or
     * approved-but-not-yet-finalized — the earlier, more fixable moment to
     * catch this, before a second one gets finalized into a duplicate Loan.
     */
    private function reportDuplicateRequests(): bool
    {
        $requests = LoanRequest::whereIn('status', ['pending', 'approved'])
            ->whereNull('archived_at')
            ->whereDoesntHave('loan')
            ->with('farmer')
            ->get()
            ->groupBy('farmer_id')
            ->filter(fn ($group) => $group->count() > 1);

        if ($requests->isEmpty()) {
            $this->info('No farmers have more than one pending/approved-unfinalized loan request.');

            return false;
        }

        $this->warn("Farmers with more than one pending/approved-unfinalized loan request ({$requests->count()}):");

        $rows = $requests->flatMap(fn ($group) => $group->map(fn (LoanRequest $request) => [
            $request->farmer?->full_name ?? "Farmer #{$request->farmer_id}",
            'LN-'.str_pad((string) $request->id, 3, '0', STR_PAD_LEFT),
            $request->status,
            peso($request->requested_amount),
            $request->created_at->format('M d, Y'),
        ]));

        $this->table(['Farmer', 'Request ID', 'Status', 'Amount', 'Created'], $rows);

        return true;
    }
}
