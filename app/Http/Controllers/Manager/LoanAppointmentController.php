<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\LoanAppointment;
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
            ->orderBy('appointment_date')
            ->orderBy('appointment_time');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date('date'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('farmer', function ($f) use ($search) {
                        $f->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
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
     * Cancel an appointment on the cooperative's behalf (e.g. unavailable slot).
     */
    public function cancel(LoanAppointment $loan_appointment)
    {
        abort_if($loan_appointment->status === 'cancelled', 422, 'This appointment is already cancelled.');

        $loan_appointment->update(['status' => 'cancelled']);

        $name = $loan_appointment->user->farmer?->full_name ?? $loan_appointment->user->name;

        return redirect()->route('manager.loan-appointment')
            ->with('success', "Appointment for {$name} has been cancelled.");
    }
}
