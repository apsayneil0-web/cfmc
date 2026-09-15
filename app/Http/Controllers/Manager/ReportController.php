<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use App\Models\Loan;
use App\Models\Machine;
use App\Models\ScheduleRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $reportType = in_array($request->get('report_type'), ['harvesting', 'loan', 'maintenance'])
            ? $request->get('report_type')
            : 'harvesting';

        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $memberId = $request->get('member_id');
        $loanType = $request->get('loan_type');
        $loanStatus = $request->get('loan_status');
        $paymentStatus = $request->get('payment_status');

        $farmers = Farmer::where('status', 'approved')->orderBy('last_name')->get();
        $selectedFarmer = $memberId ? $farmers->firstWhere('id', $memberId) : null;

        $rows = match ($reportType) {
            'harvesting' => $this->harvestingRows($dateFrom, $dateTo, $memberId),
            'loan' => $this->loanRows($dateFrom, $dateTo, $memberId, $loanType, $loanStatus, $paymentStatus),
            'maintenance' => $this->maintenanceRows($dateFrom, $dateTo),
        };

        $breakdown = match ($reportType) {
            'harvesting' => $this->yieldByFarmer($rows),
            'loan' => $this->loanStatusBreakdown($rows),
            'maintenance' => $this->maintenanceTierBreakdown($rows),
        };

        $summary = $reportType === 'loan' ? $this->loanSummary($rows) : null;

        return view('manager.reporting', compact(
            'reportType', 'dateFrom', 'dateTo', 'memberId', 'loanType', 'loanStatus', 'paymentStatus',
            'farmers', 'selectedFarmer', 'rows', 'breakdown', 'summary'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $reportType = in_array($request->get('report_type'), ['harvesting', 'loan', 'maintenance'])
            ? $request->get('report_type')
            : 'harvesting';

        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $memberId = $request->get('member_id');
        $loanType = $request->get('loan_type');
        $loanStatus = $request->get('loan_status');
        $paymentStatus = $request->get('payment_status');

        $rows = match ($reportType) {
            'harvesting' => $this->harvestingRows($dateFrom, $dateTo, $memberId),
            'loan' => $this->loanRows($dateFrom, $dateTo, $memberId, $loanType, $loanStatus, $paymentStatus),
            'maintenance' => $this->maintenanceRows($dateFrom, $dateTo),
        };

        $filename = "{$reportType}-report-".now()->format('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($reportType, $rows) {
            $handle = fopen('php://output', 'w');

            match ($reportType) {
                'harvesting' => $this->writeHarvestingCsv($handle, $rows),
                'loan' => $this->writeLoanCsv($handle, $rows),
                'maintenance' => $this->writeMaintenanceCsv($handle, $rows),
            };

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Completed machinery bookings with a recorded harvest yield.
     */
    private function harvestingRows(?string $dateFrom, ?string $dateTo, ?string $memberId)
    {
        $query = ScheduleRequest::with('user.farmer')
            ->whereNull('archived_at')
            ->whereNotNull('harvest_yield');

        if ($dateFrom) {
            $query->whereDate('scheduled_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('scheduled_date', '<=', $dateTo);
        }
        if ($memberId) {
            $query->whereHas('user.farmer', fn ($q) => $q->where('id', $memberId));
        }

        return $query->orderByDesc('scheduled_date')->get();
    }

    /**
     * Finalized, disbursed loans, filtered by disbursement date. Loans still
     * awaiting disbursement are excluded outright (not just by date range) —
     * their remaining_balance/next_due_date aren't set yet, so principal,
     * interest, and balance figures wouldn't mean anything for them.
     * loan_type filters against loan_requests.purpose (free text — the app
     * has no dedicated loan-product-type field yet); payment_status is
     * derived from amount_paid/remaining_balance, so it's applied to the
     * collection after the DB query rather than in SQL.
     */
    private function loanRows(?string $dateFrom, ?string $dateTo, ?string $memberId, ?string $loanType = null, ?string $loanStatus = null, ?string $paymentStatus = null)
    {
        $query = Loan::with(['loanRequest.farmer', 'payments'])
            ->whereNull('archived_at')
            ->whereNotNull('disbursed_at');

        if ($dateFrom) {
            $query->whereDate('disbursed_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('disbursed_at', '<=', $dateTo);
        }
        if ($memberId) {
            $query->whereHas('loanRequest', fn ($q) => $q->where('farmer_id', $memberId));
        }
        if ($loanType) {
            $query->whereHas('loanRequest', fn ($q) => $q->where('purpose', 'like', "%{$loanType}%"));
        }
        if ($loanStatus) {
            $query->where('status', $loanStatus);
        }

        $rows = $query->orderByDesc('created_at')->get();

        if ($paymentStatus) {
            $rows = $rows->filter(fn (Loan $loan) => match ($paymentStatus) {
                'paid' => (float) $loan->remaining_balance <= 0,
                'partial' => (float) $loan->remaining_balance > 0 && $loan->amount_paid > 0,
                'unpaid' => (float) $loan->remaining_balance > 0 && $loan->amount_paid <= 0,
                default => true,
            })->values();
        }

        return $rows;
    }

    /**
     * Per-machine usage within the date range, alongside the machine's
     * current (all-time) maintenance tier — the service policy is based on
     * lifetime use, not the report's date window.
     */
    private function maintenanceRows(?string $dateFrom, ?string $dateTo)
    {
        return Machine::whereNull('archived_at')->get()->map(function (Machine $machine) use ($dateFrom, $dateTo) {
            $completed = $machine->scheduleRequests()->where('status', 'completed')->whereNull('archived_at');

            if ($dateFrom) {
                $completed->whereDate('scheduled_date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $completed->whereDate('scheduled_date', '<=', $dateTo);
            }

            $bookings = $completed->get();
            $hours = round($bookings->sum(
                fn (ScheduleRequest $b) => Carbon::parse($b->start_time)->diffInMinutes(Carbon::parse($b->end_time)) / 60
            ), 1);

            return (object) [
                'machine' => $machine,
                'times_used' => $bookings->count(),
                'usage_hours' => $hours,
            ];
        });
    }

    private function yieldByFarmer($rows)
    {
        return $rows->groupBy(fn ($r) => $r->display_name)
            ->map(fn ($group, $name) => (object) ['label' => $name, 'value' => $group->sum('harvest_yield')])
            ->sortByDesc('value')
            ->take(5)
            ->values();
    }

    /**
     * Grouped by display_status (Active/Overdue/Partial/Paid) rather than the
     * raw lifecycle status, so it lines up with the Status column shown in
     * the loan report table.
     */
    private function loanStatusBreakdown($rows)
    {
        $order = ['Active', 'Overdue', 'Partial', 'Paid'];

        return $rows->groupBy('display_status')
            ->map(fn ($group, $status) => (object) ['label' => $status, 'value' => $group->count()])
            ->sortBy(fn ($item) => array_search($item->label, $order))
            ->values();
    }

    /**
     * Totals for the loan report's Report Summary panel.
     */
    private function loanSummary($rows)
    {
        return (object) [
            'total_loans' => $rows->count(),
            'total_principal' => round((float) $rows->sum('principal_amount'), 2),
            'total_interest' => round((float) $rows->sum('interest_charged'), 2),
            'total_penalties' => round((float) $rows->sum('penalty_charged'), 2),
            'total_paid' => round((float) $rows->sum('amount_paid'), 2),
            'total_outstanding' => round((float) $rows->sum('remaining_balance'), 2),
        ];
    }

    private function maintenanceTierBreakdown($rows)
    {
        $labels = ['none' => 'Not Yet Used', 'routine' => 'Routine', 'basic' => 'Basic', 'full' => 'Full', 'comprehensive' => 'Comprehensive'];

        return $rows->groupBy(fn ($r) => $r->machine->maintenance_level)
            ->map(fn ($group, $level) => (object) ['label' => $labels[$level] ?? ucfirst($level), 'value' => $group->count()])
            ->sortByDesc('value')
            ->values();
    }

    private function writeHarvestingCsv($handle, $rows): void
    {
        fputcsv($handle, ['Date', 'Farmer', 'Machinery', 'Land Size (ha)', 'Harvest Yield', 'Location']);
        foreach ($rows as $row) {
            fputcsv($handle, [
                $row->scheduled_date->format('Y-m-d'),
                $row->display_name,
                $row->machinery,
                $row->land_size,
                $row->harvest_yield,
                $row->location,
            ]);
        }
    }

    private function writeLoanCsv($handle, $rows): void
    {
        fputcsv($handle, ['Loan', 'Farmer', 'Loan Type', 'Principal', 'Interest', 'Penalty', 'Total Amount', 'Amount Paid', 'Remaining Balance', 'Next Due Date', 'Status']);
        foreach ($rows as $row) {
            fputcsv($handle, [
                'LN-'.str_pad((string) $row->id, 3, '0', STR_PAD_LEFT),
                $row->farmer?->full_name,
                $row->loanRequest?->purpose,
                $row->principal_amount,
                $row->interest_charged,
                $row->penalty_charged,
                $row->total_amount,
                $row->amount_paid,
                $row->remaining_balance,
                $row->remaining_balance > 0 ? $row->next_due_date?->format('Y-m-d') : null,
                $row->display_status,
            ]);
        }
    }

    private function writeMaintenanceCsv($handle, $rows): void
    {
        fputcsv($handle, ['Machine', 'Times Used (in range)', 'Hours (in range)', 'Maintenance Tier']);
        foreach ($rows as $row) {
            fputcsv($handle, [
                $row->machine->name,
                $row->times_used,
                $row->usage_hours,
                $row->machine->maintenance_label,
            ]);
        }
    }
}
