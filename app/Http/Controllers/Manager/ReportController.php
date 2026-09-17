<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Cbu;
use App\Models\CbuTransaction;
use App\Models\Complaint;
use App\Models\Expense;
use App\Models\Farmer;
use App\Models\HarvestPayment;
use App\Models\Loan;
use App\Models\Machine;
use App\Models\ScheduleRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private const REPORT_TYPES = ['harvesting', 'loan', 'maintenance', 'schedule', 'cbu', 'complaint', 'expense', 'harvest_payment'];

    public function index(Request $request)
    {
        $reportType = in_array($request->get('report_type'), self::REPORT_TYPES, true)
            ? $request->get('report_type')
            : 'harvesting';

        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $memberId = $request->get('member_id');
        $machineId = $request->get('machine_id');
        $loanStatus = $request->get('loan_status');
        $paymentStatus = $request->get('payment_status');
        $category = $request->get('category');

        $farmers = Farmer::where('status', 'approved')->orderBy('last_name')->get();
        $selectedFarmer = $memberId ? $farmers->firstWhere('id', $memberId) : null;
        $machines = Machine::whereNull('archived_at')->orderBy('name')->get();
        $selectedMachine = $machineId ? $machines->firstWhere('id', $machineId) : null;

        $rows = $this->rowsFor($reportType, $dateFrom, $dateTo, $memberId, $machineId, $loanStatus, $paymentStatus, $category);

        $breakdown = match ($reportType) {
            'harvesting' => $this->yieldByFarmer($rows),
            'loan' => $this->loanStatusBreakdown($rows),
            'maintenance' => $this->maintenanceTierBreakdown($rows),
            'schedule' => $this->scheduleMemberBreakdown($rows),
            'cbu' => $this->cbuCategoryBreakdown($rows),
            'complaint' => $this->complaintCommonProblems($rows),
            'expense' => $this->expenseCategoryBreakdown($rows),
            'harvest_payment' => $this->harvestPaymentMemberBreakdown($rows),
        };

        $breakdownTitle = match ($reportType) {
            'harvesting' => 'Top Yield by Farmer',
            'loan' => 'Loan Status Breakdown',
            'maintenance' => 'Maintenance Tier Breakdown',
            'schedule' => 'Members vs Non-Members',
            'cbu' => 'CBU Category Breakdown',
            'complaint' => 'Common Problems',
            'expense' => 'Expense Category Breakdown',
            'harvest_payment' => 'Payments by Farmer Type',
        };

        $summary = match ($reportType) {
            'loan' => $this->loanSummary($rows),
            'schedule' => $this->scheduleSummary($rows),
            'cbu' => $this->cbuSummary($rows),
            'complaint' => $this->complaintSummary($rows),
            'expense' => $this->expenseSummary($rows),
            'harvest_payment' => $this->harvestPaymentSummary($rows),
            default => null,
        };

        return view('manager.reporting', compact(
            'reportType', 'dateFrom', 'dateTo', 'memberId', 'machineId', 'loanStatus', 'paymentStatus', 'category',
            'farmers', 'selectedFarmer', 'machines', 'selectedMachine', 'rows', 'breakdown', 'breakdownTitle', 'summary'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $reportType = in_array($request->get('report_type'), self::REPORT_TYPES, true)
            ? $request->get('report_type')
            : 'harvesting';

        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $memberId = $request->get('member_id');
        $machineId = $request->get('machine_id');
        $loanStatus = $request->get('loan_status');
        $paymentStatus = $request->get('payment_status');
        $category = $request->get('category');

        $rows = $this->rowsFor($reportType, $dateFrom, $dateTo, $memberId, $machineId, $loanStatus, $paymentStatus, $category);

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
                'schedule' => $this->writeScheduleCsv($handle, $rows),
                'cbu' => $this->writeCbuCsv($handle, $rows),
                'complaint' => $this->writeComplaintCsv($handle, $rows),
                'expense' => $this->writeExpenseCsv($handle, $rows),
                'harvest_payment' => $this->writeHarvestPaymentCsv($handle, $rows),
            };

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Single dispatch point shared by index() and export() so the two never
     * drift out of sync on which filters apply to which report type.
     */
    private function rowsFor(string $reportType, ?string $dateFrom, ?string $dateTo, ?string $memberId, ?string $machineId, ?string $loanStatus, ?string $paymentStatus, ?string $category)
    {
        return match ($reportType) {
            'harvesting' => $this->harvestingRows($dateFrom, $dateTo, $memberId),
            'loan' => $this->loanRows($dateFrom, $dateTo, $memberId, $loanStatus, $paymentStatus),
            'maintenance' => $this->maintenanceRows($dateFrom, $dateTo, $machineId),
            'schedule' => $this->scheduleRows($dateFrom, $dateTo, $memberId),
            'cbu' => $this->cbuRows($dateFrom, $dateTo, $memberId),
            'complaint' => $this->complaintRows($dateFrom, $dateTo, $memberId),
            'expense' => $this->expenseRows($dateFrom, $dateTo, $category),
            'harvest_payment' => $this->harvestPaymentRows($dateFrom, $dateTo, $memberId),
        };
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
     * payment_status is derived from amount_paid/remaining_balance, so it's
     * applied to the collection after the DB query rather than in SQL.
     */
    private function loanRows(?string $dateFrom, ?string $dateTo, ?string $memberId, ?string $loanStatus = null, ?string $paymentStatus = null)
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
     * lifetime use, not the report's date window. Narrowed to a single
     * machine when machine_id is given.
     */
    private function maintenanceRows(?string $dateFrom, ?string $dateTo, ?string $machineId = null)
    {
        return Machine::whereNull('archived_at')
            ->when($machineId, fn ($q) => $q->where('id', $machineId))
            ->get()
            ->map(function (Machine $machine) use ($dateFrom, $dateTo) {
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

    /**
     * Machinery bookings that actually occupied capacity (approved or
     * completed — denied/cancelled requests never used a machine), for
     * tallying member vs non-member usage within the date range.
     */
    private function scheduleRows(?string $dateFrom, ?string $dateTo, ?string $memberId)
    {
        $query = ScheduleRequest::with(['user.farmer', 'machine'])
            ->whereNull('archived_at')
            ->whereIn('status', ['approved', 'completed']);

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
     * CBU ledger entries (contributions and expenses/withdrawals) within the
     * date range. The schema has no separate "dividend"/"savings" transaction
     * type — those are recorded as contributions with a free-text category,
     * which is what the category breakdown groups by.
     */
    private function cbuRows(?string $dateFrom, ?string $dateTo, ?string $memberId)
    {
        $query = CbuTransaction::with(['cbu.farmer', 'recordedBy']);

        if ($dateFrom) {
            $query->whereDate('transaction_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('transaction_date', '<=', $dateTo);
        }
        if ($memberId) {
            $query->whereHas('cbu', fn ($q) => $q->where('farmer_id', $memberId));
        }

        return $query->orderByDesc('transaction_date')->orderByDesc('created_at')->get();
    }

    /**
     * Farmer complaints submitted for manager review (drafts stay private to
     * the farmer, mirroring ComplaintController@index), within the date range.
     */
    private function complaintRows(?string $dateFrom, ?string $dateTo, ?string $memberId)
    {
        $query = Complaint::with('user.farmer')->where('status', '!=', 'draft');

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }
        if ($memberId) {
            $query->whereHas('user.farmer', fn ($q) => $q->where('id', $memberId));
        }

        return $query->orderByDesc('created_at')->get();
    }

    /**
     * Cooperative operating expenses within the date range, optionally
     * narrowed to one category (operational/machinery/replaceable_parts).
     */
    private function expenseRows(?string $dateFrom, ?string $dateTo, ?string $category)
    {
        $query = Expense::with('recordedBy');

        if ($dateFrom) {
            $query->whereDate('expense_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('expense_date', '<=', $dateTo);
        }
        if ($category) {
            $query->where('category', $category);
        }

        return $query->orderByDesc('expense_date')->orderByDesc('created_at')->get();
    }

    /**
     * The cooperative's cut of reported harvest income (9% member / 12%
     * non-member), within the date range. member_id only narrows members —
     * non-member entries have no farmer_id to match against.
     */
    private function harvestPaymentRows(?string $dateFrom, ?string $dateTo, ?string $memberId)
    {
        $query = HarvestPayment::with('farmer');

        if ($dateFrom) {
            $query->whereDate('payment_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('payment_date', '<=', $dateTo);
        }
        if ($memberId) {
            $query->where('farmer_id', $memberId);
        }

        return $query->orderByDesc('payment_date')->orderByDesc('created_at')->get();
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

    private function maintenanceTierBreakdown($rows)
    {
        $labels = ['none' => 'Not Yet Used', 'routine' => 'Routine', 'basic' => 'Basic', 'full' => 'Full', 'comprehensive' => 'Comprehensive'];

        return $rows->groupBy(fn ($r) => $r->machine->maintenance_level)
            ->map(fn ($group, $level) => (object) ['label' => $labels[$level] ?? ucfirst($level), 'value' => $group->count()])
            ->sortByDesc('value')
            ->values();
    }

    private function scheduleMemberBreakdown($rows)
    {
        $labels = ['member' => 'Member', 'non-member' => 'Non-Member'];

        return $rows->groupBy('member_type')
            ->map(fn ($group, $type) => (object) ['label' => $labels[$type] ?? ucfirst($type), 'value' => $group->count()])
            ->sortByDesc('value')
            ->values();
    }

    private function cbuCategoryBreakdown($rows)
    {
        return $rows->groupBy(fn ($r) => $r->category ?: 'Uncategorized')
            ->map(fn ($group, $category) => (object) ['label' => $category, 'value' => $group->count()])
            ->sortByDesc('value')
            ->take(6)
            ->values();
    }

    /**
     * Most frequently reported issues, by complaint subject — the closest
     * thing to a "common problems" category the schema supports, since
     * subject is free text farmers type themselves rather than a fixed list.
     */
    private function complaintCommonProblems($rows)
    {
        return $rows->groupBy('subject')
            ->map(fn ($group, $subject) => (object) ['label' => $subject, 'value' => $group->count()])
            ->sortByDesc('value')
            ->take(5)
            ->values();
    }

    private function expenseCategoryBreakdown($rows)
    {
        $labels = ['operational' => 'Operational', 'machinery' => 'Machinery', 'replaceable_parts' => 'Replaceable Parts'];

        return $rows->groupBy('category')
            ->map(fn ($group, $cat) => (object) ['label' => $labels[$cat] ?? ucfirst($cat), 'value' => $group->count()])
            ->sortByDesc('value')
            ->values();
    }

    private function harvestPaymentMemberBreakdown($rows)
    {
        $labels = ['member' => 'Member', 'non-member' => 'Non-Member'];

        return $rows->groupBy('member_type')
            ->map(fn ($group, $type) => (object) ['label' => $labels[$type] ?? ucfirst($type), 'value' => $group->count()])
            ->sortByDesc('value')
            ->values();
    }

    /**
     * Totals for the loan report's Report Summary panel.
     */
    private function loanSummary($rows)
    {
        return (object) ['rows' => [
            ['label' => 'Total Loans', 'value' => (string) $rows->count()],
            ['label' => 'Total Principal', 'value' => peso($rows->sum('principal_amount'))],
            ['label' => 'Total Interest', 'value' => peso($rows->sum('interest_charged'))],
            ['label' => 'Total Penalties', 'value' => peso($rows->sum('penalty_charged'))],
            ['label' => 'Total Amount Paid', 'value' => peso($rows->sum('amount_paid'))],
            ['label' => 'Total Outstanding Balance', 'value' => peso($rows->sum('remaining_balance')), 'emphasis' => true],
        ]];
    }

    private function scheduleSummary($rows)
    {
        return (object) ['rows' => [
            ['label' => 'Total Bookings', 'value' => (string) $rows->count()],
            ['label' => 'Total Land Size Serviced', 'value' => number_format((float) $rows->sum('land_size'), 2).' ha'],
            ['label' => 'Members', 'value' => (string) $rows->where('member_type', 'member')->count()],
            ['label' => 'Non-Members', 'value' => (string) $rows->where('member_type', 'non-member')->count()],
        ]];
    }

    private function cbuSummary($rows)
    {
        $contributions = (float) $rows->where('type', 'contribution')->sum('amount');
        $expenses = (float) $rows->where('type', 'expense')->sum('amount');

        return (object) ['rows' => [
            ['label' => 'Total Transactions', 'value' => (string) $rows->count()],
            ['label' => 'Total Contributions', 'value' => peso($contributions)],
            ['label' => 'Total Withdrawals/Expenses', 'value' => peso($expenses)],
            ['label' => 'Net Change', 'value' => peso($contributions - $expenses), 'emphasis' => true],
            ['label' => 'Current Balance, All Members', 'value' => peso(Cbu::sum('balance'))],
        ]];
    }

    private function complaintSummary($rows)
    {
        $resolved = $rows->where('status', 'resolved')->count();

        return (object) ['rows' => [
            ['label' => 'Total Complaints', 'value' => (string) $rows->count()],
            ['label' => 'Resolved', 'value' => (string) $resolved],
            ['label' => 'Pending', 'value' => (string) ($rows->count() - $resolved), 'emphasis' => true],
        ]];
    }

    private function expenseSummary($rows)
    {
        return (object) ['rows' => [
            ['label' => 'Total Records', 'value' => (string) $rows->count()],
            ['label' => 'Operational', 'value' => peso($rows->where('category', 'operational')->sum('amount'))],
            ['label' => 'Machinery', 'value' => peso($rows->where('category', 'machinery')->sum('amount'))],
            ['label' => 'Replaceable Parts', 'value' => peso($rows->where('category', 'replaceable_parts')->sum('amount'))],
            ['label' => 'Total Amount', 'value' => peso($rows->sum('amount')), 'emphasis' => true],
        ]];
    }

    private function harvestPaymentSummary($rows)
    {
        $memberPayments = (float) $rows->where('member_type', 'member')->sum('payment_amount');
        $nonMemberPayments = (float) $rows->where('member_type', 'non-member')->sum('payment_amount');

        return (object) ['rows' => [
            ['label' => 'Total Records', 'value' => (string) $rows->count()],
            ['label' => 'Total Harvest Income Reported', 'value' => peso($rows->sum('harvest_amount'))],
            ['label' => 'Member Payments Collected (9%)', 'value' => peso($memberPayments)],
            ['label' => 'Non-Member Payments Collected (12%)', 'value' => peso($nonMemberPayments)],
            ['label' => 'Total Payment Collected', 'value' => peso($memberPayments + $nonMemberPayments), 'emphasis' => true],
        ]];
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

    private function writeScheduleCsv($handle, $rows): void
    {
        fputcsv($handle, ['Date', 'Requester', 'Member Type', 'Machine', 'Land Size (ha)', 'Status']);
        foreach ($rows as $row) {
            fputcsv($handle, [
                $row->scheduled_date->format('Y-m-d'),
                $row->display_name,
                $row->member_type === 'member' ? 'Member' : 'Non-Member',
                $row->machine?->name ?? $row->machinery,
                $row->land_size,
                ucfirst($row->status),
            ]);
        }
    }

    private function writeCbuCsv($handle, $rows): void
    {
        fputcsv($handle, ['Date', 'Farmer', 'Type', 'Category', 'Amount', 'Balance After']);
        foreach ($rows as $row) {
            fputcsv($handle, [
                $row->transaction_date->format('Y-m-d'),
                $row->cbu?->farmer?->full_name,
                ucfirst($row->type),
                $row->category ?? '—',
                $row->amount,
                $row->balance_after,
            ]);
        }
    }

    private function writeComplaintCsv($handle, $rows): void
    {
        fputcsv($handle, ['Date', 'Farmer', 'Subject', 'Status', 'Manager Response']);
        foreach ($rows as $row) {
            fputcsv($handle, [
                $row->created_at->format('Y-m-d'),
                $row->user?->farmer?->full_name ?? $row->user?->name,
                $row->subject,
                ucfirst(str_replace('_', ' ', $row->status)),
                $row->manager_response,
            ]);
        }
    }

    private function writeExpenseCsv($handle, $rows): void
    {
        fputcsv($handle, ['Date', 'Category', 'Description', 'Amount', 'Status', 'Recorded By']);
        foreach ($rows as $row) {
            fputcsv($handle, [
                $row->expense_date->format('Y-m-d'),
                ucfirst(str_replace('_', ' ', $row->category)),
                $row->description,
                $row->amount,
                ucfirst($row->status),
                $row->recordedBy?->name,
            ]);
        }
    }

    private function writeHarvestPaymentCsv($handle, $rows): void
    {
        fputcsv($handle, ['Date', 'Farmer', 'Farmer Type', 'Harvest Amount', 'Rate (%)', 'Payment Amount', 'Notes']);
        foreach ($rows as $row) {
            fputcsv($handle, [
                $row->payment_date->format('Y-m-d'),
                $row->payer_name,
                $row->member_type === 'member' ? 'Member' : 'Non-Member',
                $row->harvest_amount,
                $row->rate,
                $row->payment_amount,
                $row->notes,
            ]);
        }
    }
}
