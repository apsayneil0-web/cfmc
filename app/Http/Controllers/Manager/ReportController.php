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

        $farmers = Farmer::where('status', 'approved')->orderBy('last_name')->get();

        $rows = match ($reportType) {
            'harvesting' => $this->harvestingRows($dateFrom, $dateTo, $memberId),
            'loan' => $this->loanRows($dateFrom, $dateTo, $memberId),
            'maintenance' => $this->maintenanceRows($dateFrom, $dateTo),
        };

        $breakdown = match ($reportType) {
            'harvesting' => $this->yieldByFarmer($rows),
            'loan' => $this->loanStatusBreakdown($rows),
            'maintenance' => $this->maintenanceTierBreakdown($rows),
        };

        return view('manager.reporting', compact('reportType', 'dateFrom', 'dateTo', 'memberId', 'farmers', 'rows', 'breakdown'));
    }

    public function export(Request $request): StreamedResponse
    {
        $reportType = in_array($request->get('report_type'), ['harvesting', 'loan', 'maintenance'])
            ? $request->get('report_type')
            : 'harvesting';

        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $memberId = $request->get('member_id');

        $rows = match ($reportType) {
            'harvesting' => $this->harvestingRows($dateFrom, $dateTo, $memberId),
            'loan' => $this->loanRows($dateFrom, $dateTo, $memberId),
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
     * Finalized loans, filtered by disbursement date.
     */
    private function loanRows(?string $dateFrom, ?string $dateTo, ?string $memberId)
    {
        $query = Loan::with('loanRequest.farmer')->whereNull('archived_at');

        if ($dateFrom) {
            $query->whereDate('disbursed_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('disbursed_at', '<=', $dateTo);
        }
        if ($memberId) {
            $query->whereHas('loanRequest', fn ($q) => $q->where('farmer_id', $memberId));
        }

        return $query->orderByDesc('created_at')->get();
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

    private function loanStatusBreakdown($rows)
    {
        $labels = ['active' => 'Active', 'fully_paid' => 'Fully Paid', 'overdue' => 'Overdue', 'pending_disbursement' => 'Pending Disbursement'];

        return $rows->groupBy('status')
            ->map(fn ($group, $status) => (object) ['label' => $labels[$status] ?? ucfirst($status), 'value' => $group->count()])
            ->sortByDesc('value')
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
        fputcsv($handle, ['Loan', 'Farmer', 'Principal', 'Remaining Balance', 'Status', 'Disbursed At']);
        foreach ($rows as $row) {
            fputcsv($handle, [
                'LN-'.str_pad((string) $row->id, 3, '0', STR_PAD_LEFT),
                $row->farmer?->full_name,
                $row->principal_amount,
                $row->remaining_balance,
                $row->status,
                $row->disbursed_at?->format('Y-m-d'),
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
