<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\LoanApprovalController;
use App\Http\Controllers\Farmer\LoanAppointmentController as FarmerLoanAppointmentController;
use App\Http\Controllers\Manager\LoanAppointmentController as ManagerLoanAppointmentController;
use App\Http\Controllers\Manager\LoanRequestController;
use App\Models\Cbu;
use App\Models\Farmer;
use App\Models\Loan;
use App\Models\LoanAppointment;
use App\Models\LoanRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * Cooperative's one-loan-at-a-time rule: a farmer may have only one loan "in
 * process" (a pending/approved-unfinalized request, an unpaid loan, or an
 * open appointment) at once, regardless of purpose, until the previous loan
 * is fully paid. These tests exercise the real controller methods directly
 * (not HTTP/routing/CSRF) so they're testing the actual business logic in
 * Farmer::loanInProcess() and every place that calls it.
 */
class OneLoanAtATimeTest extends TestCase
{
    use RefreshDatabase;

    private function makeManager(): User
    {
        return User::create([
            'username' => 'manager_'.uniqid(),
            'name' => 'Test Manager',
            'password' => bcrypt('password'),
            'roleID' => 2,
            'status' => 'active',
        ]);
    }

    /**
     * A farmer with a login account and ₱8,000 CBU balance — eligible for a
     * Regular Loan up to ₱16,000 (CBU eligibility isn't what these tests are
     * about, so it's kept comfortably clear of that rule's edges).
     */
    private function makeFarmer(): Farmer
    {
        $account = User::create([
            'username' => 'farmer_'.uniqid(),
            'name' => 'Test Farmer',
            'password' => bcrypt('password'),
            'roleID' => 3,
            'status' => 'active',
        ]);

        $farmer = Farmer::create([
            'first_name' => 'Test',
            'last_name' => 'Farmer',
            'contact_number' => '09123456789',
            'land_area' => 1,
            'province' => 'South Cotabato',
            'municipality' => 'Surallah',
            'status' => 'approved',
            'account_user_id' => $account->id,
        ]);

        Cbu::create(['farmer_id' => $farmer->id, 'balance' => 8000, 'status' => 'active']);

        return $farmer->fresh();
    }

    private function makeLoanRequest(Farmer $farmer, string $status, array $overrides = []): LoanRequest
    {
        return LoanRequest::create(array_merge([
            'farmer_id' => $farmer->id,
            'type' => 'regular',
            'requested_amount' => 5000,
            'purpose' => 'Seeds & Fertilizer',
            'repayment_terms_months' => 6,
            'collateral' => 'motor',
            'status' => $status,
        ], $overrides));
    }

    private function makeLoan(LoanRequest $loanRequest, string $status): Loan
    {
        return Loan::create([
            'loan_request_id' => $loanRequest->id,
            'principal_amount' => $loanRequest->requested_amount,
            'interest_rate' => 2,
            'repayment_terms_months' => $loanRequest->repayment_terms_months,
            'installment_amount' => round($loanRequest->requested_amount / $loanRequest->repayment_terms_months, 2),
            'status' => $status,
            'remaining_balance' => $status === 'fully_paid' ? 0 : $loanRequest->requested_amount,
        ]);
    }

    private function newRequestPayload(Farmer $farmer): Request
    {
        $request = new Request();
        $request->merge([
            'farmer_id' => $farmer->id,
            'type' => 'regular',
            'requested_amount' => 5000,
            'purpose' => 'Seeds & Fertilizer',
            'repayment_terms_months' => 6,
            'collateral' => 'motor',
        ]);
        $request->files->set('documents', UploadedFile::fake()->create('id.jpg', 50, 'image/jpeg'));

        return $request;
    }

    public function test_manager_cannot_create_request_when_farmer_has_pending_request(): void
    {
        $farmer = $this->makeFarmer();
        $this->makeLoanRequest($farmer, 'pending');
        Auth::login($this->makeManager());

        $this->expectException(ValidationException::class);
        app(LoanRequestController::class)->store($this->newRequestPayload($farmer));
    }

    public function test_manager_cannot_create_request_when_farmer_has_approved_unfinalized_request(): void
    {
        // This is the bug: an approved request with no Loan yet used to not
        // block a second request from being created.
        $farmer = $this->makeFarmer();
        $this->makeLoanRequest($farmer, 'approved');
        Auth::login($this->makeManager());

        $this->expectException(ValidationException::class);

        try {
            app(LoanRequestController::class)->store($this->newRequestPayload($farmer));
        } catch (ValidationException $e) {
            $this->assertStringContainsString('awaiting finalization', $e->errors()['farmer_id'][0]);

            throw $e;
        }
    }

    public function test_submit_to_admin_fails_when_farmer_has_approved_unfinalized_request(): void
    {
        $farmer = $this->makeFarmer();
        $this->makeLoanRequest($farmer, 'approved');

        $appointment = LoanAppointment::create([
            'user_id' => $farmer->account_user_id,
            'appointment_date' => now()->addDays(3)->toDateString(),
            'appointment_time' => '08:00',
            'purpose' => 'Loan Discussion',
            'status' => 'approved',
            'requested_amount' => 5000,
            'loan_purpose' => 'Seeds & Fertilizer',
            'repayment_terms_months' => 6,
            'collateral' => 'motor',
        ]);

        Auth::login($this->makeManager());

        $response = app(LoanRequestController::class)->storeFromAppointment($appointment);

        $this->assertTrue($response->getSession()->has('errors'));
        $this->assertNull($appointment->fresh()->loan_request_id);
        $this->assertSame(1, LoanRequest::where('farmer_id', $farmer->id)->count());
    }

    public function test_manager_cannot_create_request_when_farmer_has_unpaid_loan(): void
    {
        $farmer = $this->makeFarmer();
        $existing = $this->makeLoanRequest($farmer, 'approved');
        $this->makeLoan($existing, 'active');
        Auth::login($this->makeManager());

        $this->expectException(ValidationException::class);

        try {
            app(LoanRequestController::class)->store($this->newRequestPayload($farmer));
        } catch (ValidationException $e) {
            $this->assertStringContainsString('Has an unpaid loan', $e->errors()['farmer_id'][0]);

            throw $e;
        }
    }

    public function test_finalize_refuses_when_farmer_already_has_unpaid_loan(): void
    {
        $farmer = $this->makeFarmer();

        $alreadyLoaned = $this->makeLoanRequest($farmer, 'approved');
        $this->makeLoan($alreadyLoaned, 'active');

        // The bug's exact signature: a second approved-but-unfinalized
        // request for the same farmer, predating the create-time fix.
        $duplicate = $this->makeLoanRequest($farmer, 'approved');

        Auth::login($this->makeManager());

        $this->expectException(HttpException::class);

        try {
            app(LoanRequestController::class)->finalize(new Request([
                'principal_amount' => 5000,
                'interest_rate' => 2,
                'repayment_terms_months' => 6,
            ]), $duplicate);
        } catch (HttpException $e) {
            $this->assertSame(422, $e->getStatusCode());
            $this->assertStringContainsString('already has an unpaid loan', $e->getMessage());

            throw $e;
        } finally {
            $this->assertSame(0, Loan::where('loan_request_id', $duplicate->id)->count());
        }
    }

    public function test_farmer_cannot_book_appointment_with_unpaid_loan_in_process(): void
    {
        $farmer = $this->makeFarmer();
        $existing = $this->makeLoanRequest($farmer, 'approved');
        $this->makeLoan($existing, 'active');

        Auth::login(User::find($farmer->account_user_id));

        $request = new Request();
        $request->merge([
            'appointment_date' => now()->addDays(3)->toDateString(),
            'appointment_time' => '08:00',
            'purpose' => 'Loan Discussion',
            'requested_amount' => 5000,
            'loan_purpose' => 'Seeds & Fertilizer',
            'repayment_terms_months' => 6,
            'collateral' => 'motor',
        ]);
        $request->files->set('documents', UploadedFile::fake()->create('proof.jpg', 50, 'image/jpeg'));

        $this->expectException(ValidationException::class);

        try {
            app(FarmerLoanAppointmentController::class)->store($request);
        } catch (ValidationException $e) {
            $this->assertStringContainsString('already have a loan in process', $e->errors()['requested_amount'][0]);

            throw $e;
        }
    }

    public function test_farmer_cannot_book_appointment_with_request_in_process(): void
    {
        $farmer = $this->makeFarmer();
        $this->makeLoanRequest($farmer, 'pending');

        Auth::login(User::find($farmer->account_user_id));

        $request = new Request();
        $request->merge([
            'appointment_date' => now()->addDays(3)->toDateString(),
            'appointment_time' => '08:00',
            'purpose' => 'Loan Discussion',
            'requested_amount' => 5000,
            'loan_purpose' => 'Seeds & Fertilizer',
            'repayment_terms_months' => 6,
            'collateral' => 'motor',
        ]);
        $request->files->set('documents', UploadedFile::fake()->create('proof.jpg', 50, 'image/jpeg'));

        $this->expectException(ValidationException::class);
        app(FarmerLoanAppointmentController::class)->store($request);
    }

    public function test_farmer_can_request_again_once_previous_loan_is_fully_paid(): void
    {
        $farmer = $this->makeFarmer();
        $paidOff = $this->makeLoanRequest($farmer, 'approved');
        $this->makeLoan($paidOff, 'fully_paid');

        Auth::login($this->makeManager());

        app(LoanRequestController::class)->store($this->newRequestPayload($farmer));

        $this->assertSame(2, LoanRequest::where('farmer_id', $farmer->id)->count());
        $this->assertSame(1, LoanRequest::where('farmer_id', $farmer->id)->where('status', 'pending')->count());
    }

    public function test_farmer_can_book_appointment_again_once_previous_loan_is_fully_paid(): void
    {
        $farmer = $this->makeFarmer();
        $paidOff = $this->makeLoanRequest($farmer, 'approved');
        $this->makeLoan($paidOff, 'fully_paid');

        Auth::login(User::find($farmer->account_user_id));

        $request = new Request();
        $request->merge([
            'appointment_date' => now()->addDays(3)->toDateString(),
            'appointment_time' => '08:00',
            'purpose' => 'Loan Discussion',
            'requested_amount' => 5000,
            'loan_purpose' => 'Seeds & Fertilizer',
            'repayment_terms_months' => 6,
            'collateral' => 'motor',
        ]);
        $request->files->set('documents', UploadedFile::fake()->create('proof.jpg', 50, 'image/jpeg'));

        app(FarmerLoanAppointmentController::class)->store($request);

        $this->assertSame(1, LoanAppointment::where('user_id', $farmer->account_user_id)->count());
    }

    public function test_editing_an_existing_pending_request_does_not_block_itself(): void
    {
        $farmer = $this->makeFarmer();
        $pending = $this->makeLoanRequest($farmer, 'pending');
        Auth::login($this->makeManager());

        $request = new Request();
        $request->merge([
            'farmer_id' => $farmer->id,
            'type' => 'regular',
            'requested_amount' => 6000,
            'purpose' => 'Seeds & Fertilizer',
            'repayment_terms_months' => 6,
            'collateral' => 'motor',
        ]);

        app(LoanRequestController::class)->update($request, $pending);

        $this->assertSame(6000.0, (float) $pending->fresh()->requested_amount);
    }

    public function test_admin_cannot_approve_request_when_farmer_already_has_unpaid_loan(): void
    {
        $farmer = $this->makeFarmer();
        $existing = $this->makeLoanRequest($farmer, 'approved');
        $this->makeLoan($existing, 'active');
        $pending = $this->makeLoanRequest($farmer, 'pending');

        $this->expectException(HttpException::class);

        try {
            app(LoanApprovalController::class)->approve($pending);
        } catch (HttpException $e) {
            $this->assertSame(422, $e->getStatusCode());

            throw $e;
        } finally {
            $this->assertSame('pending', $pending->fresh()->status);
        }
    }

    public function test_manager_cannot_approve_appointment_when_farmer_already_has_unpaid_loan(): void
    {
        $farmer = $this->makeFarmer();
        $existing = $this->makeLoanRequest($farmer, 'approved');
        $this->makeLoan($existing, 'active');

        $appointment = LoanAppointment::create([
            'user_id' => $farmer->account_user_id,
            'appointment_date' => now()->addDays(3)->toDateString(),
            'appointment_time' => '09:00',
            'purpose' => 'Loan Discussion',
            'status' => 'pending',
        ]);

        $this->expectException(HttpException::class);

        try {
            app(ManagerLoanAppointmentController::class)->approve($appointment);
        } catch (HttpException $e) {
            $this->assertSame(422, $e->getStatusCode());

            throw $e;
        } finally {
            $this->assertSame('pending', $appointment->fresh()->status);
        }
    }
}
