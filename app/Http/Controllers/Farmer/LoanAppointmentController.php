<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Models\LoanAppointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanAppointmentController extends Controller
{
    /**
     * Mirrors LoanRequestController's PURPOSES/TERMS lists so a farmer's
     * self-submitted loan pre-request uses the same options a manager would
     * pick from when encoding it for real.
     */
    private const LOAN_PURPOSES = ['Farming Equipment', 'Seeds & Fertilizer', 'Machinery Purchase', 'Working Capital'];

    private const LOAN_TERMS = [6, 12, 18, 24];

    public function index()
    {
        $appointments = LoanAppointment::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('farmer.loan-appointment', [
            'appointments' => $appointments,
            'loanPurposes' => self::LOAN_PURPOSES,
            'loanTerms' => self::LOAN_TERMS,
            'loanEligibility' => Auth::user()->farmer?->regular_loan_eligibility,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateAppointment($request);

        $documentsPath = null;
        if ($request->hasFile('documents')) {
            $document = $request->file('documents');
            $documentsPath = $document->storeAs('loan_documents', time().'_'.$document->getClientOriginalName(), 'public');
        }

        LoanAppointment::create([
            'user_id' => Auth::id(),
            'appointment_date' => $validated['appointment_date'],
            'appointment_time' => $validated['appointment_time'],
            'purpose' => $validated['purpose'],
            'requested_amount' => $validated['requested_amount'],
            'loan_purpose' => $validated['loan_purpose'],
            'repayment_terms_months' => $validated['repayment_terms_months'],
            'collateral' => $validated['collateral'] ?? null,
            'documents_path' => $documentsPath,
            'status' => 'pending',
        ]);

        return redirect()->route('farmer.loan-appointment')
            ->with('success', 'Loan appointment request submitted successfully!');
    }

    public function update(Request $request, LoanAppointment $loan_appointment)
    {
        abort_if($loan_appointment->user_id !== Auth::id(), 403);
        abort_if($loan_appointment->status !== 'pending', 422, 'Only pending appointments can be rescheduled.');

        $validated = $this->validateAppointment($request);

        $documentsPath = $loan_appointment->documents_path;
        if ($request->hasFile('documents')) {
            $document = $request->file('documents');
            $documentsPath = $document->storeAs('loan_documents', time().'_'.$document->getClientOriginalName(), 'public');
        }

        unset($validated['documents']);

        $loan_appointment->update([
            ...$validated,
            'documents_path' => $documentsPath,
        ]);

        return redirect()->route('farmer.loan-appointment')
            ->with('success', 'Appointment rescheduled successfully!');
    }

    private function validateAppointment(Request $request): array
    {
        return $request->validate([
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
            'purpose' => 'required|string|max:255',
            'requested_amount' => [
                'required',
                'numeric',
                'min:1',
                function ($attribute, $value, $fail) {
                    $farmer = Auth::user()->farmer;

                    if (! $farmer) {
                        return;
                    }

                    // Same Regular Loan CBU eligibility rule the Manager's
                    // Loan Request form enforces, checked here too so a
                    // farmer never pre-requests an amount that would just
                    // get rejected later when the Manager submits it.
                    $eligibility = $farmer->regular_loan_eligibility;

                    if (! $eligibility['eligible']) {
                        $fail("Your CBU balance is ".peso($eligibility['cbu_balance']).". A minimum of ".peso($eligibility['min_cbu'])." is required to request a loan.");

                        return;
                    }

                    if ($value > $eligibility['max_loanable']) {
                        $fail("Your CBU balance is ".peso($eligibility['cbu_balance']).". The maximum loan you can request is ".peso($eligibility['max_loanable']).".");
                    }
                },
            ],
            'loan_purpose' => ['required', 'string', 'in:'.implode(',', self::LOAN_PURPOSES)],
            'repayment_terms_months' => ['required', 'integer', 'in:'.implode(',', self::LOAN_TERMS)],
            'collateral' => 'nullable|string|max:255',
            'documents' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);
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
