<?php

namespace App\Console\Commands;

use App\Models\Loan;
use Illuminate\Console\Command;

class ProcessLoanDelinquency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loans:process-delinquency';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Apply overdue interest and the grace-period penalty/escalation policy to every active loan';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $loans = Loan::whereNull('archived_at')
            ->whereIn('status', ['active', 'overdue'])
            ->with('loanRequest.farmer')
            ->get();

        foreach ($loans as $loan) {
            $loan->applyOverdueInterest();
            $loan->applyGracePeriodPolicy();
        }

        $this->info("Processed delinquency policy for {$loans->count()} loan(s).");

        return self::SUCCESS;
    }
}
