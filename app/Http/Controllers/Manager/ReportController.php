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
use Illuminate\Pagination\LengthAwarePaginator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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

        $farmers = Farmer::where('status', 'approved')
            ->whereDoesntHave('account', fn ($q) => $q->where('status', 'inactive'))
            ->orderBy('last_name')
            ->get();
        $selectedFarmer = $memberId ? $farmers->firstWhere('id', $memberId) : null;
        $machines = Machine::whereNull('archived_at')->orderBy('name')->get();
        $selectedMachine = $machineId ? $machines->firstWhere('id', $machineId) : null;

        $rows = $this->rowsFor($reportType, $dateFrom, $dateTo, $memberId, $machineId, $loanStatus, $paymentStatus, $category);
        $breakdown = $this->breakdownFor($reportType, $rows);
        $breakdownTitle = $this->breakdownTitleFor($reportType);
        $summary = $this->summaryFor($reportType, $rows);

        // $rows stays the full, unpaginated collection — the breakdown and
        // summary stats above are computed from it and would be wrong if
        // they only reflected one page. $tableRows is a separate, paginated
        // copy for the report preview table only.
        $tableRows = $this->paginateCollection($rows, $request);

        return view('manager.reporting', compact(
            'reportType', 'dateFrom', 'dateTo', 'memberId', 'machineId', 'loanStatus', 'paymentStatus', 'category',
            'farmers', 'selectedFarmer', 'machines', 'selectedMachine', 'rows', 'tableRows', 'breakdown', 'breakdownTitle', 'summary'
        ));
    }

    private function paginateCollection($items, Request $request, int $perPage = 10): LengthAwarePaginator
    {
        $page = (int) $request->input('page', 1);

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
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
     * Branded Excel export: loads the cooperative's letterhead-styled
     * template (storage/app/report_templates/CFMC_Report_Templates.xlsx —
     * one sheet per report type, designed outside the app) and writes this
     * report's filtered rows into it, rather than generating a workbook
     * from scratch. Keeps every sheet's exact formatting, formulas, and
     * signature lines; only the data area and the two header-row formulas
     * (SUM range, generated-on date) are touched.
     */
    public function exportExcel(Request $request): BinaryFileResponse
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

        $farmers = Farmer::where('status', 'approved')
            ->whereDoesntHave('account', fn ($q) => $q->where('status', 'inactive'))
            ->orderBy('last_name')
            ->get();
        $selectedFarmer = $memberId ? $farmers->firstWhere('id', $memberId) : null;
        $machines = Machine::whereNull('archived_at')->orderBy('name')->get();
        $selectedMachine = $machineId ? $machines->firstWhere('id', $machineId) : null;

        $rows = $this->rowsFor($reportType, $dateFrom, $dateTo, $memberId, $machineId, $loanStatus, $paymentStatus, $category);
        $data = $this->excelRowsFor($reportType, $rows);
        $summary = $this->summaryFor($reportType, $rows);
        $breakdown = $this->breakdownFor($reportType, $rows);
        $config = $this->excelSheetConfig($reportType);
        $filtersText = $this->excelFiltersText($reportType, $selectedFarmer, $selectedMachine, $loanStatus, $paymentStatus, $category);

        $spreadsheet = IOFactory::load(storage_path('app/report_templates/CFMC_Report_Templates.xlsx'));
        $sheet = $spreadsheet->getSheetByName($config['sheet']);
        abort_if($sheet === null, 500, "Report template is missing its \"{$config['sheet']}\" sheet.");

        // Keep only the sheet being exported — the workbook ships with all
        // 8 report sheets as a reusable template, but one export is one report.
        foreach ($spreadsheet->getSheetNames() as $name) {
            if ($name !== $config['sheet']) {
                $spreadsheet->removeSheetByIndex($spreadsheet->getIndex($spreadsheet->getSheetByName($name)));
            }
        }
        $spreadsheet->setActiveSheetIndex(0);

        $sheet->setCellValue('B5', $this->excelPeriodText($dateFrom, $dateTo));
        $sheet->setCellValue($config['generatedCell'], ExcelDate::PHPToExcel(now()));
        $sheet->setCellValue('B6', $filtersText);

        $startRow = 9;
        $templateDataRows = 10;
        $dataCount = count($data);

        // The template reserves 10 data rows (9-18) before a blank spacer and
        // the Report Summary / Breakdown / Notes / Signature blocks below.
        // More rows than that need extra ones inserted (copying row 18's
        // styling), which pushes every one of those later blocks down with
        // it — so nothing below the table needs its own overflow handling.
        $shift = max(0, $dataCount - $templateDataRows);
        if ($shift > 0) {
            $sheet->insertNewRowBefore($startRow + $templateDataRows, $shift);
            foreach ($config['columns'] as $col) {
                for ($r = $startRow + $templateDataRows; $r < $startRow + $templateDataRows + $shift; $r++) {
                    $sheet->duplicateStyle($sheet->getStyle($col.($startRow + $templateDataRows - 1)), "{$col}{$r}");
                }
            }
        }

        // Clear every reserved data row first so a filtered result smaller
        // than the reserved 10 rows never leaves old content behind.
        $lastDataRow = $startRow - 1 + max($dataCount, $templateDataRows);
        foreach ($config['columns'] as $col) {
            for ($r = $startRow; $r <= $lastDataRow; $r++) {
                $sheet->setCellValue("{$col}{$r}", null);
            }
        }

        foreach ($data as $i => $values) {
            $row = $startRow + $i;
            foreach ($values as $colIdx => $value) {
                $col = $config['columns'][$colIdx];
                if ($value instanceof \DateTimeInterface) {
                    $sheet->setCellValue("{$col}{$row}", ExcelDate::PHPToExcel($value));
                } else {
                    $sheet->setCellValue("{$col}{$row}", $value);
                }
            }
        }

        // Report Summary block: template bakes in the section header only —
        // both the label and the (already-formatted) value for every row are
        // written here, reusing the exact same data the on-screen panel shows.
        $summaryHeaderRow = $lastDataRow + 2;
        foreach ($summary->rows as $i => $row) {
            $r = $summaryHeaderRow + 1 + $i;
            $sheet->setCellValue("A{$r}", $row['label']);
            $sheet->setCellValue("C{$r}", $row['value']);
            if (! empty($row['emphasis'])) {
                $sheet->getStyle("C{$r}")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFB00020'));
            }
        }

        // Breakdown block: header + 2-column mini-header are baked into the
        // template; both the label and count for every row are written here
        // since several breakdowns (top yield, common problems) have labels
        // that vary with the data, not a fixed category list.
        $breakdownHeaderRow = $summaryHeaderRow + $config['summaryCount'] + 2;
        $breakdownStartRow = $breakdownHeaderRow + 2;
        foreach ($breakdown as $i => $item) {
            $r = $breakdownStartRow + $i;
            $sheet->setCellValue("A{$r}", $item->label);
            $sheet->setCellValue("C{$r}", $item->value);
        }

        // Prepared by: the template bakes in the label and role/org line —
        // only the name itself (the logged-in manager) is written here.
        $notesRow = $breakdownStartRow + $config['breakdownCount'] + 1;
        $signRow = $notesRow + $config['noteCount'] + 2;
        $sheet->setCellValue('A'.($signRow + 1), auth()->user()->name ?? '');

        $filename = "{$reportType}-report-".now()->format('Y-m-d').'.xlsx';
        $tempPath = tempnam(sys_get_temp_dir(), 'cfmc_report_').'.xlsx';
        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($tempPath);

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    /**
     * "Jan 01, 2026  to  Jun 30, 2026" style text for the Report Period
     * cell — human-readable, matching how the rest of the sheet reads,
     * rather than raw From/To values.
     */
    private function excelPeriodText(?string $dateFrom, ?string $dateTo): string
    {
        return match (true) {
            $dateFrom && $dateTo => Carbon::parse($dateFrom)->format('M d, Y').'  to  '.Carbon::parse($dateTo)->format('M d, Y'),
            (bool) $dateFrom => 'From '.Carbon::parse($dateFrom)->format('M d, Y'),
            (bool) $dateTo => 'Through '.Carbon::parse($dateTo)->format('M d, Y'),
            default => 'All available records',
        };
    }

    /**
     * "Farmer: X  |  Loan Status: Y  |  ..." summary of whichever filters
     * were actually applied, for the Filters Applied row.
     */
    private function excelFiltersText(string $reportType, ?Farmer $selectedFarmer, ?Machine $selectedMachine, ?string $loanStatus, ?string $paymentStatus, ?string $category): string
    {
        return match ($reportType) {
            'maintenance' => 'Machine: '.($selectedMachine?->name ?? 'All Machines'),
            'expense' => 'Category: '.($category ? ucfirst(str_replace('_', ' ', $category)) : 'All Categories'),
            'loan' => 'Farmer: '.($selectedFarmer?->full_name ?? 'All Farmers')
                .'  |  Loan Status: '.($loanStatus ? ucfirst(str_replace('_', ' ', $loanStatus)) : 'All')
                .'  |  Payment Status: '.($paymentStatus ? ucfirst($paymentStatus) : 'All (Paid / Partial / Unpaid)'),
            default => 'Farmer: '.($selectedFarmer?->full_name ?? 'All Farmers'),
        };
    }

    /**
     * Which sheet in the Excel template each report type fills, which
     * column letters its rows use (matching excelRowsFor()'s value order),
     * where the "Generated on" date goes, and how many rows the Report
     * Summary / Breakdown / Note sections reserve — all taken from the
     * template's actual layout (storage/app/report_templates/CFMC_Report_Templates.xlsx).
     * summaryCount must equal that type's summaryFor()->rows count, and
     * breakdownCount must be a true upper bound on breakdownFor()'s size
     * (fixed category lists, or a ->take(N) cap) — there's no overflow
     * handling for the breakdown block like there is for the data table.
     */
    private function excelSheetConfig(string $reportType): array
    {
        return match ($reportType) {
            'harvesting' => ['sheet' => 'Harvesting', 'columns' => ['A', 'B', 'C', 'D', 'E', 'F'], 'generatedCell' => 'F5', 'summaryCount' => 3, 'breakdownCount' => 5, 'noteCount' => 2],
            'harvest_payment' => ['sheet' => 'Harvest Payment', 'columns' => ['A', 'B', 'C', 'D', 'E', 'F', 'G'], 'generatedCell' => 'G5', 'summaryCount' => 5, 'breakdownCount' => 2, 'noteCount' => 2],
            'loan' => ['sheet' => 'Loan', 'columns' => ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'], 'generatedCell' => 'J5', 'summaryCount' => 6, 'breakdownCount' => 4, 'noteCount' => 2],
            'maintenance' => ['sheet' => 'Maintenance', 'columns' => ['A', 'B', 'C', 'D'], 'generatedCell' => 'D5', 'summaryCount' => 3, 'breakdownCount' => 5, 'noteCount' => 2],
            'schedule' => ['sheet' => 'Schedule', 'columns' => ['A', 'B', 'C', 'D', 'E', 'F'], 'generatedCell' => 'F5', 'summaryCount' => 4, 'breakdownCount' => 2, 'noteCount' => 1],
            'cbu' => ['sheet' => 'Capital Build-Up', 'columns' => ['A', 'B', 'C', 'D', 'E', 'F'], 'generatedCell' => 'F5', 'summaryCount' => 5, 'breakdownCount' => 6, 'noteCount' => 2],
            'complaint' => ['sheet' => 'Complaint', 'columns' => ['A', 'B', 'C', 'D', 'E'], 'generatedCell' => 'E5', 'summaryCount' => 3, 'breakdownCount' => 5, 'noteCount' => 2],
            'expense' => ['sheet' => 'Expense', 'columns' => ['A', 'B', 'C', 'D', 'E', 'F'], 'generatedCell' => 'F5', 'summaryCount' => 5, 'breakdownCount' => 3, 'noteCount' => 2],
        };
    }

    /**
     * Row values for the Excel export, in the same column order as
     * excelSheetConfig()'s 'columns' list for that report type. Mirrors
     * each writeXCsv() method's data exactly — the Excel and CSV exports
     * should never show different numbers for the same filtered rows.
     */
    private function excelRowsFor(string $reportType, $rows): array
    {
        return match ($reportType) {
            'harvesting' => $rows->map(fn ($r) => [
                $r->scheduled_date, $r->display_name, $r->machinery, $r->land_size, $r->harvest_yield, $r->location,
            ])->values()->all(),
            'harvest_payment' => $rows->map(fn ($r) => [
                $r->payment_date, $r->payer_name, $r->member_type === 'member' ? 'Member' : 'Non-Member',
                $r->harvest_amount, $r->rate, $r->payment_amount, $r->notes,
            ])->values()->all(),
            'loan' => $rows->map(fn (Loan $r) => [
                $r->farmer?->full_name, $r->loanRequest?->purpose,
                $r->principal_amount, $r->interest_charged, $r->penalty_charged, $r->total_amount, $r->amount_paid,
                $r->remaining_balance, $r->remaining_balance > 0 ? $r->next_due_date : null, $r->display_status,
            ])->values()->all(),
            'maintenance' => $rows->map(fn ($r) => [
                $r->machine->name, $r->times_used, $r->usage_hours, $r->machine->maintenance_label,
            ])->values()->all(),
            'schedule' => $rows->map(fn ($r) => [
                $r->scheduled_date, $r->display_name, $r->member_type === 'member' ? 'Member' : 'Non-Member',
                $r->machine?->name ?? $r->machinery, $r->land_size, ucfirst($r->status),
            ])->values()->all(),
            'cbu' => $rows->map(fn ($r) => [
                $r->transaction_date, $r->cbu?->farmer?->full_name, ucfirst($r->type), $r->category ?? '—', $r->amount, $r->balance_after,
            ])->values()->all(),
            'complaint' => $rows->map(fn ($r) => [
                $r->created_at, $r->user?->farmer?->full_name ?? $r->user?->name, $r->subject,
                ucfirst(str_replace('_', ' ', $r->status)), $r->manager_response,
            ])->values()->all(),
            'expense' => $rows->map(fn ($r) => [
                $r->expense_date, ucfirst(str_replace('_', ' ', $r->category)), $r->description, $r->amount,
                ucfirst($r->status), $r->recordedBy?->name,
            ])->values()->all(),
        };
    }

    /**
     * Shared by index() and exportExcel() so the on-screen Report Summary /
     * Breakdown panels and the Excel export's equivalent sections can never
     * show different numbers for the same filtered rows.
     */
    private function breakdownFor(string $reportType, $rows)
    {
        return match ($reportType) {
            'harvesting' => $this->yieldByFarmer($rows),
            'loan' => $this->loanStatusBreakdown($rows),
            'maintenance' => $this->maintenanceTierBreakdown($rows),
            'schedule' => $this->scheduleMemberBreakdown($rows),
            'cbu' => $this->cbuCategoryBreakdown($rows),
            'complaint' => $this->complaintCommonProblems($rows),
            'expense' => $this->expenseCategoryBreakdown($rows),
            'harvest_payment' => $this->harvestPaymentMemberBreakdown($rows),
        };
    }

    private function breakdownTitleFor(string $reportType): string
    {
        return match ($reportType) {
            'harvesting' => 'Top Yield by Farmer',
            'loan' => 'Loan Status Breakdown',
            'maintenance' => 'Maintenance Tier Breakdown',
            'schedule' => 'Members vs Non-Members',
            'cbu' => 'CBU Category Breakdown',
            'complaint' => 'Common Problems',
            'expense' => 'Expense Category Breakdown',
            'harvest_payment' => 'Payments by Farmer Type',
        };
    }

    private function summaryFor(string $reportType, $rows)
    {
        return match ($reportType) {
            'harvesting' => $this->harvestingSummary($rows),
            'loan' => $this->loanSummary($rows),
            'maintenance' => $this->maintenanceSummary($rows),
            'schedule' => $this->scheduleSummary($rows),
            'cbu' => $this->cbuSummary($rows),
            'complaint' => $this->complaintSummary($rows),
            'expense' => $this->expenseSummary($rows),
            'harvest_payment' => $this->harvestPaymentSummary($rows),
        };
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
            ->whereNotNull('harvest_yield')
            ->visibleAccount();

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
            ->whereNotNull('disbursed_at')
            ->visibleAccount();

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
            ->whereIn('status', ['approved', 'completed'])
            ->visibleAccount();

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
        $query = CbuTransaction::with(['cbu.farmer', 'recordedBy'])->visibleAccount();

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
        $query = Complaint::with('user.farmer')->where('status', '!=', 'draft')->visibleAccount();

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
        $query = HarvestPayment::with('farmer')->visibleAccount();

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

    private function harvestingSummary($rows)
    {
        return (object) ['rows' => [
            ['label' => 'Total Harvest Records', 'value' => (string) $rows->count()],
            ['label' => 'Total Land Size Harvested', 'value' => number_format((float) $rows->sum('land_size'), 2).' ha'],
            ['label' => 'Total Harvest Yield', 'value' => (string) $rows->sum('harvest_yield')],
        ]];
    }

    private function maintenanceSummary($rows)
    {
        return (object) ['rows' => [
            ['label' => 'Total Machines', 'value' => (string) $rows->count()],
            ['label' => 'Total Times Used', 'value' => (string) $rows->sum('times_used')],
            ['label' => 'Total Hours Used', 'value' => (string) $rows->sum('usage_hours')],
        ]];
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
            ['label' => 'Current Balance, All Members', 'value' => peso(Cbu::visibleAccount()->sum('balance'))],
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
