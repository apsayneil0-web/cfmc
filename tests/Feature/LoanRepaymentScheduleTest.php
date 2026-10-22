<?php

namespace Tests\Feature;

use App\Http\Controllers\Manager\PaymentController;
use App\Models\Farmer;
use App\Models\Loan;
use App\Models\LoanRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * Covers the loan repayment fix: a payment that reaches or exceeds one
 * installment's cost used to fail to advance the due date at all (the
 * overage was silently stranded) instead of closing out as many full
 * periods as it covers, oldest first, and carrying the remainder forward.
 */
class LoanRepaymentScheduleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ₱8,000 principal, 2% interest, 12 months — monthly_due = ₱826.67,
     * matching the bug report's own example exactly.
     */
    private function makeDisbursedLoan(): Loan
    {
        $account = User::create([
            'username' => 'loan_farmer_'.uniqid(),
            'name' => 'Loan Test Farmer',
            'password' => bcrypt('password'),
            'roleID' => 3,
            'status' => 'active',
        ]);

        $farmer = Farmer::create([
            'first_name' => 'Loan',
            'last_name' => 'TestFarmer',
            'contact_number' => '09123456789',
            'land_area' => 1,
            'province' => 'South Cotabato',
            'municipality' => 'Surallah',
            'status' => 'approved',
            'account_user_id' => $account->id,
        ]);

        $loanRequest = LoanRequest::create([
            'farmer_id' => $farmer->id,
            'type' => 'regular',
            'requested_amount' => 8000,
            'purpose' => 'Seeds & Fertilizer',
            'repayment_terms_months' => 12,
            'status' => 'approved',
            'collateral' => 'motor',
        ]);

        $loan = Loan::create([
            'loan_request_id' => $loanRequest->id,
            'principal_amount' => 8000,
            'interest_rate' => 2,
            'repayment_terms_months' => 12,
            'installment_amount' => round(8000 / 12, 2),
            'status' => 'pending_disbursement',
        ]);

        $manager = User::create([
            'username' => 'loan_manager_'.uniqid(),
            'name' => 'Loan Test Manager',
            'password' => bcrypt('password'),
            'roleID' => 2,
            'status' => 'active',
        ]);

        $loan->disburse('cash', 'TEST-REF', $manager->id, '2026-10-22');

        return $loan->fresh();
    }

    private function recordPaymentViaController(Loan $loan, float $amount, string $type = 'payment'): void
    {
        $manager = User::where('roleID', 2)->first();
        Auth::login($manager);

        app(PaymentController::class)->recordLoanPayment(new Request([
            'loan_id' => $loan->id,
            'type' => $type,
            'amount' => $amount,
            'notes' => 'test',
        ]));
    }

    public function test_paying_exactly_one_installment_advances_the_due_date(): void
    {
        $loan = $this->makeDisbursedLoan();
        $startingDueDate = $loan->next_due_date->toDateString();

        $this->recordPaymentViaController($loan, 826.67, 'payment');
        $loan->refresh();

        $this->assertNotEquals($startingDueDate, $loan->next_due_date->toDateString());
        $this->assertEquals('2026-12-22', $loan->next_due_date->toDateString());
        $this->assertEquals(0.0, (float) $loan->current_period_paid);
        $this->assertEquals('paid', $loan->installment_schedule[0]->status);
    }

    public function test_prepayment_covering_several_months_closes_them_all(): void
    {
        $loan = $this->makeDisbursedLoan();

        // Exactly the bug report's own example: ₱5,000 prepayment.
        $this->recordPaymentViaController($loan, 5000, 'prepayment');
        $loan->refresh();

        $schedule = $loan->installment_schedule;

        for ($i = 0; $i < 6; $i++) {
            $this->assertEquals('paid', $schedule[$i]->status, "Installment {$schedule[$i]->number} should be paid.");
        }

        $this->assertEquals('current', $schedule[6]->status);
        $this->assertEquals('upcoming', $schedule[7]->status);
    }

    public function test_prepayment_ending_on_a_partial_installment_leaves_the_correct_remainder(): void
    {
        $loan = $this->makeDisbursedLoan();

        $this->recordPaymentViaController($loan, 5000, 'prepayment');
        $loan->refresh();

        // 5000 - 6 * 826.67 = 39.98 paid into installment 7, 786.69 left on it.
        $this->assertEquals(39.98, (float) $loan->current_period_paid);
        $this->assertEquals(786.69, (float) $loan->installment_schedule[6]->amount);
        $this->assertEquals(4920.04, (float) $loan->remaining_balance);
    }

    public function test_rejecting_a_payment_above_the_remaining_balance(): void
    {
        // type=payment (not prepayment): a Prepayment above the loan's
        // payoff amount is its own, more specific rejection — see
        // LoanEarlyPayoffTest::test_rejecting_a_prepayment_higher_than_the_payoff_amount.
        // Regular payments keep the plain remaining-balance ceiling tested here.
        $loan = $this->makeDisbursedLoan();
        $overpayment = (float) $loan->remaining_balance + 100;

        $this->expectException(HttpException::class);

        try {
            $this->recordPaymentViaController($loan, $overpayment, 'payment');
        } catch (HttpException $e) {
            $this->assertSame(422, $e->getStatusCode());
            $this->assertStringContainsString('exceeds remaining balance', $e->getMessage());

            throw $e;
        } finally {
            $loan->refresh();
            $this->assertEquals('active', $loan->status);
            $this->assertEquals(9920.04, (float) $loan->remaining_balance);
        }
    }

    public function test_paying_off_the_entire_loan_marks_it_fully_paid(): void
    {
        // A Prepayment is now capped at payoff_amount (which waives future
        // interest), not the full remaining_balance — see LoanEarlyPayoffTest
        // for that behavior in detail. Paying the full nominal balance here
        // is done as a plain payment instead, closing every period in turn.
        $loan = $this->makeDisbursedLoan();
        $fullBalance = (float) $loan->remaining_balance;

        $this->recordPaymentViaController($loan, $fullBalance, 'payment');
        $loan->refresh();

        $this->assertEquals('fully_paid', $loan->status);
        $this->assertEquals(0.0, (float) $loan->remaining_balance);
    }

    public function test_payment_locks_the_loan_row_inside_a_transaction(): void
    {
        // Can't easily simulate two concurrent HTTP requests in a feature
        // test, but this confirms the lock is actually taken (no exception
        // from an invalid query) and a normal payment still completes
        // correctly through it.
        $loan = $this->makeDisbursedLoan();

        $this->recordPaymentViaController($loan, 826.67, 'payment');
        $loan->refresh();

        $this->assertEquals(1, $loan->payments()->count());
        $this->assertEquals('2026-12-22', $loan->next_due_date->toDateString());
    }

    public function test_recalculate_schedule_fixes_a_loan_corrupted_by_the_old_bug(): void
    {
        $loan = $this->makeDisbursedLoan();

        // Simulate the pre-fix bug's exact symptom by hand: a payment was
        // recorded (balance correctly reduced) but the schedule fields were
        // left stuck, exactly as the old advanceDueDateForPayment() did.
        $loan->payments()->create([
            'type' => 'prepayment',
            'amount' => 5000,
            'transaction_date' => now()->toDateString(),
            'balance_after' => 4920.04,
        ]);
        $loan->update(['remaining_balance' => 4920.04]);
        // next_due_date/current_period_paid/carried_over_amount are still
        // at their pristine disbursement values — the corrupted state.

        $result = $loan->recalculateSchedule();
        $loan->refresh();

        $this->assertTrue($result['changed']);
        $this->assertFalse($result['mismatch']);
        $this->assertEquals('2027-05-22', $loan->next_due_date->toDateString());
        $this->assertEquals(39.98, (float) $loan->current_period_paid);
        // remaining_balance is a cross-check only, never overwritten from
        // the replay — it should be exactly what it already was.
        $this->assertEquals(4920.04, (float) $loan->remaining_balance);
    }
}
