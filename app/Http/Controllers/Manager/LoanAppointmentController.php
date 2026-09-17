<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\LoanAppointment;
use App\Models\Notification;
use Illuminate\Http\Request;

class LoanAppointmentController extends Controller
{
    /**
     * Display loan appointment requests farmers have submitted for review.
     */
    public function index(Request $request)
    {
        $query = LoanAppointment::with('user.farmer')
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'cancelled')")
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date('date'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "{$search}%")
                    ->orWhere('name', 'like', "% {$search}%")
                    ->orWhereHas('farmer', function ($f) use ($search) {
                        $f->where('first_name', 'like', "{$search}%")
                            ->orWhere('last_name', 'like', "{$search}%");
                    });
            });
        }

        $appointments = $query->get();

        $stats = [
            'pending_count' => LoanAppointment::where('status', 'pending')->count(),
            'approved_count' => LoanAppointment::where('status', 'approved')->count(),
            'upcoming_count' => LoanAppointment::where('status', 'approved')
                ->whereDate('appointment_date', '>=', now()->toDateString())
                ->count(),
        ];

        return view('manager.loan-appointment', compact('appointments', 'stats'));
    }

    /**
     * Confirm a farmer's requested appointment.
     */
    public function approve(LoanAppointment $loan_appointment)
    {
        abort_if($loan_appointment->status !== 'pending', 422, 'Only pending appointments can be approved.');

        $loan_appointment->update(['status' => 'approved']);

        $name = $loan_appointment->user->farmer?->full_name ?? $loan_appointment->user->name;

        return redirect()->route('manager.loan-appointment')
            ->with('success', "Appointment for {$name} has been approved.");
    }

    /**
     * Reschedule an appointment on the cooperative's behalf (e.g. slot
     * conflict, farmer called in asking to move it).
     */
    public function reschedule(Request $request, LoanAppointment $loan_appointment)
    {
        abort_if($loan_appointment->status === 'cancelled', 422, 'Cancelled appointments cannot be rescheduled.');
        abort_if($loan_appointment->loan_request_id, 422, 'This appointment has already been submitted to the Administrator and can no longer be rescheduled.');

        $oldDate = $loan_appointment->appointment_date->format('M d, Y');
        $oldTime = \Carbon\Carbon::parse($loan_appointment->appointment_time)->format('g:i A');

        $validated = $request->validate([
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => [
                'required',
                'in:'.implode(',', LoanAppointment::SLOTS),
                function ($attribute, $value, $fail) use ($request, $loan_appointment) {
                    if (! $request->filled('appointment_date')) {
                        return;
                    }

                    $available = LoanAppointment::availableSlotsFor($request->input('appointment_date'), $loan_appointment->id);

                    if (! in_array($value, $available, true)) {
                        $fail('That time slot is already taken for the selected date. This date allows up to 5 appointments per day, one per slot.');
                    }
                },
            ],
        ]);

        $loan_appointment->update($validated);

        $name = $loan_appointment->user->farmer?->full_name ?? $loan_appointment->user->name;
        $newDate = $loan_appointment->appointment_date->format('M d, Y');
        $newTime = \Carbon\Carbon::parse($loan_appointment->appointment_time)->format('g:i A');

        Notification::create([
            'user_id' => $loan_appointment->user_id,
            'title' => 'Your Loan Appointment Has Been Rescheduled',
            'message' => "Your appointment originally set for {$oldDate} at {$oldTime} has been moved to {$newDate} at {$newTime}.",
            'type' => 'reminder',
            'is_read' => false,
            'created_at' => now(),
        ]);

        return redirect()->route('manager.loan-appointment')
            ->with('success', "Appointment for {$name} has been rescheduled.");
    }
}
