<?php

namespace App\Console\Commands;

use App\Models\Loan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-time data repair for loans whose next_due_date/current_period_paid/
 * carried_over_amount were corrupted by advanceDueDateForPayment()'s
 * pre-fix bug — a payment reaching or exceeding one period's cost failed to
 * advance the due date at all, silently stranding the overage. Dry-run by
 * default; nothing is written unless --apply is passed.
 */
class RecalculateLoanSchedules extends Command
{
    protected $signature = 'loans:recalculate-schedules {--apply : Actually save the corrected values (default is a dry run)}';

    protected $description = 'Recalculate each loan\'s repayment schedule from its payment history, to fix records saved under the old payment-advancement bug';

    public function handle(): int
    {
        $apply = $this->option('apply');
        $changed = [];
        $mismatched = [];

        // Runs for real either way — recalculateSchedule() does call
        // save() — but a dry run wraps the whole thing in a transaction
        // that gets rolled back at the end, so nothing is actually
        // persisted unless --apply was given.
        DB::beginTransaction();

        $loans = Loan::whereIn('status', ['active', 'overdue', 'fully_paid'])
            ->whereNotNull('original_due_date')
            ->with('loanRequest.farmer')
            ->get();

        foreach ($loans as $loan) {
            $farmerName = $loan->farmer?->full_name ?? "Loan #{$loan->id}";
            $loanCode = 'LN-'.str_pad((string) $loan->id, 3, '0', STR_PAD_LEFT);

            $before = [
                'next_due_date' => $loan->next_due_date?->toDateString(),
                'current_period_paid' => (float) $loan->current_period_paid,
                'carried_over_amount' => (float) $loan->carried_over_amount,
            ];

            $result = $loan->recalculateSchedule();

            if ($result['mismatch']) {
                $mismatched[] = [$farmerName, $loanCode];

                continue;
            }

            $after = [
                'next_due_date' => $loan->next_due_date?->toDateString(),
                'current_period_paid' => (float) $loan->current_period_paid,
                'carried_over_amount' => (float) $loan->carried_over_amount,
            ];

            if ($before !== $after) {
                $changed[] = [
                    $farmerName,
                    $loanCode,
                    $before['next_due_date'].' → '.$after['next_due_date'],
                    peso($before['current_period_paid']).' → '.peso($after['current_period_paid']),
                    peso($before['carried_over_amount']).' → '.peso($after['carried_over_amount']),
                ];
            }
        }

        if ($apply) {
            DB::commit();
        } else {
            DB::rollBack();
        }

        if (empty($changed) && empty($mismatched)) {
            $this->info('No loans needed correction — every schedule already matches its payment history.');

            return self::SUCCESS;
        }

        if ($changed) {
            $this->warn(($apply ? 'Corrected' : 'Would correct').' '.count($changed).' loan(s):');
            $this->table(['Farmer', 'Loan ID', 'Next Due Date', 'Period Paid', 'Carried Over'], $changed);
        }

        if ($mismatched) {
            $this->newLine();
            $this->error('Skipped '.count($mismatched)." loan(s) — replaying their payment history didn't land on their actual current balance, so nothing was touched. These need a manual look instead of an automated guess:");
            $this->table(['Farmer', 'Loan ID'], $mismatched);
        }

        if (! $apply && $changed) {
            $this->newLine();
            $this->comment('This was a dry run — no changes were saved. Re-run with --apply to save them.');
        }

        return self::SUCCESS;
    }
}
