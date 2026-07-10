<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\LoanAppointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanAppointmentController extends Controller
{
    public function index()
    {
        $appointments = LoanAppointment::where('user_id', Auth::id())
            ->orderBy('appointment_date', 'desc')
            ->get();

        return view('farmer.loan-appointment', compact('appointments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
            'purpose' => 'required|string|max:255',
        ]);

        LoanAppointment::create([
            'user_id' => Auth::id(),
            'appointment_date' => $validated['appointment_date'],
            'appointment_time' => $validated['appointment_time'],
            'purpose' => $validated['purpose'],
            'status' => 'pending',
        ]);

        return redirect()->route('farmer.loan-appointment')
            ->with('success', 'Loan appointment request submitted successfully!');
    }

    public function update(Request $request, LoanAppointment $loan_appointment)
    {
        abort_if($loan_appointment->user_id !== Auth::id(), 403);
        abort_if($loan_appointment->status !== 'pending', 422, 'Only pending appointments can be rescheduled.');

        $validated = $request->validate([
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
            'purpose' => 'required|string|max:255',
        ]);

        $loan_appointment->update($validated);

        return redirect()->route('farmer.loan-appointment')
            ->with('success', 'Appointment rescheduled successfully!');
    }

    public function cancel(LoanAppointment $loan_appointment)
    {
        abort_if($loan_appointment->user_id !== Auth::id(), 403);
        abort_if($loan_appointment->status !== 'pending', 422, 'Only pending appointments can be cancelled.');

        $loan_appointment->update(['status' => 'cancelled']);

        return redirect()->route('farmer.loan-appointment')
            ->with('success', 'Appointment cancelled.');
    }
}
