<?php

namespace App\Console\Commands;

use App\Models\Loan;
use Illuminate\Console\Command;

class ProcessScheduledDisbursements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loans:process-scheduled-disbursements';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically disburse loans whose scheduled disbursement date has arrived';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $loans = Loan::where('status', 'pending_disbursement')
            ->whereNotNull('scheduled_disbursement_date')
            ->whereDate('scheduled_disbursement_date', '<=', now()->toDateString())
            ->with('loanRequest.farmer')
            ->get();

        foreach ($loans as $loan) {
            $loan->disburse(
                $loan->disbursement_method ?? 'cash',
                $loan->reference_no,
                $loan->scheduled_by,
                $loan->scheduled_disbursement_date->toDateString(),
            );
        }

        $this->info("Disbursed {$loans->count()} scheduled loan(s).");

        return self::SUCCESS;
    }
}
