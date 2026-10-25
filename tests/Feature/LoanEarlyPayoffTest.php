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
 * A Prepayment that exactly covers the loan's current payoff amount is
 * treated as an early payoff: future (not-yet-due) months' interest is
 * waived instead of being charged in full, same as the spec's own example
 * (₱8,000/12mo loan, 2 installments already paid, payoff = ₱6,826.66 not
 * ₱8,266.66 — a ₱1,440 saving).
 */
class LoanEarlyPayoffTest extends TestCase
{
    use RefreshDatabase;

    /** ₱8,000 principal, 2% interest, 12 months — monthly_due = ₱826.67. */
    private function makeDisbursedLoan(): Loan
    {
        $account = User::create([
            'username' => 'payoff_farmer_'.uniqid(),
            'name' => 'Payoff Test Farmer',
            'password' => bcrypt('password'),
            'roleID' => 3,
            'status' => 'active',
        ]);

        $farmer = Farmer::create([
            'first_name' => 'Payoff',
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
            'username' => 'payoff_manager_'.uniqid(),
            'name' => 'Payoff Test Manager',
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

    public function test_prepayment_smaller_than_one_installment_behaves_normally(): void
    {
        $loan = $this->makeDisbursedLoan();

        $this->recordPaymentViaController($loan, 300, 'prepayment');
        $loan->refresh();

        $this->assertEquals('active', $loan->status);
        $this->assertFalse($loan->paid_off_early);
        $this->assertEquals(300.0, (float) $loan->current_period_paid);
        $this->assertEquals(9620.04, (float) $loan->remaining_balance);
    }

    public function test_prepayment_covering_several_installments_without_full_payoff(): void
    {
        $loan = $this->makeDisbursedLoan();

        // 3000 covers 3 full installments (2480.01) plus a bit of a 4th —
        // well short of the ~9920 payoff amount, so this is not a payoff.
        $this->recordPaymentViaController($loan, 3000, 'prepayment');
        $loan->refresh();

        $this->assertEquals('active', $loan->status);
        $this->assertFalse($loan->paid_off_early);
        $this->assertEquals('paid', $loan->installment_schedule[2]->status);
        $this->assertEquals('current', $loan->installment_schedule[3]->status);
    }

    public function test_payoff_before_any_installment_is_paid(): void
    {
        $loan = $this->makeDisbursedLoan();
        $payoff = $loan->payoff_amount;

        // Full 12 months of interest would otherwise apply — confirm the
        // payoff amount is already less than the full remaining balance.
        $this->assertLessThan((float) $loan->remaining_balance, $payoff);

        $this->recordPaymentViaController($loan, $payoff, 'prepayment');
        $loan->refresh();

        $this->assertEquals('fully_paid', $loan->status);
        $this->assertTrue($loan->paid_off_early);
        $this->assertEquals(0.0, (float) $loan->remaining_balance);

        // Paid off on installment 1 itself: that row shows the real payoff
        // amount (not the nominal monthly_due), and the other 11 months —
        // never individually paid — collapse into one closing row instead
        // of each repeating "paid" with no amount of their own.
        $schedule = $loan->installment_schedule;
        $this->assertCount(2, $schedule);
        $this->assertEquals('paid', $schedule[0]->status);
        $this->assertEquals(1, $schedule[0]->number);
        $this->assertTrue($schedule[0]->closing);
        $this->assertEqualsWithDelta($payoff, $schedule[0]->amount, 0.01);
        $this->assertEquals('collapsed', $schedule[1]->status);
        $this->assertEquals(2, $schedule[1]->from);
        $this->assertEquals(12, $schedule[1]->to);
    }

    public function test_payoff_after_some_installments_already_paid_matches_spec_example(): void
    {
        $loan = $this->makeDisbursedLoan();

        $this->recordPaymentViaController($loan, 826.67, 'payment');
        $this->recordPaymentViaController($loan, 826.67, 'payment');
        $loan->refresh();

        // Spec's own worked example: remaining principal 6,666.66 + current
        // month interest 160 = payoff 6,826.66 (this app lands a few
        // centavos off that hand calculation — see getPayoffAmountAttribute
        // ()'s own doc comment for why — so just check it's in that
        // neighborhood and that the saving is exactly ₱1,440).
        $payoff = $loan->payoff_amount;
        $this->assertEqualsWithDelta(6826.66, $payoff, 0.10);
        $this->assertEquals(1440.0, (float) $loan->interest_saved);

        $this->recordPaymentViaController($loan, $payoff, 'prepayment');
        $loan->refresh();

        $this->assertEquals('fully_paid', $loan->status);
        $this->assertTrue($loan->paid_off_early);
    }

    public function test_payoff_while_current_installment_is_partially_paid(): void
    {
        $loan = $this->makeDisbursedLoan();

        // Only 300 toward installment 1 — leaves it "current", not closed.
        $this->recordPaymentViaController($loan, 300, 'payment');
        $loan->refresh();
        $this->assertEquals(300.0, (float) $loan->current_period_paid);

        $payoff = $loan->payoff_amount;
        $this->recordPaymentViaController($loan, $payoff, 'prepayment');
        $loan->refresh();

        $this->assertEquals('fully_paid', $loan->status);
        $this->assertTrue($loan->paid_off_early);
        $this->assertEquals(0.0, (float) $loan->current_period_paid);
        $this->assertEquals('paid', $loan->installment_schedule[0]->status);
    }

    public function test_rejecting_a_prepayment_higher_than_the_payoff_amount(): void
    {
        $loan = $this->makeDisbursedLoan();
        $tooMuch = $loan->payoff_amount + 50;

        $this->expectException(HttpException::class);

        try {
            $this->recordPaymentViaController($loan, $tooMuch, 'prepayment');
        } catch (HttpException $e) {
            $this->assertSame(422, $e->getStatusCode());
            $this->assertStringContainsString('more than it costs to pay off the loan today', $e->getMessage());
            $this->assertStringContainsString(peso($loan->payoff_amount), $e->getMessage());

            throw $e;
        } finally {
            $loan->refresh();
            $this->assertEquals('active', $loan->status);
        }
    }

    public function test_prepayment_on_an_already_completed_loan_is_rejected(): void
    {
        $loan = $this->makeDisbursedLoan();
        $this->recordPaymentViaController($loan, $loan->payoff_amount, 'prepayment');
        $loan->refresh();
        $this->assertEquals('fully_paid', $loan->status);

        $this->expectException(HttpException::class);

        try {
            $this->recordPaymentViaController($loan, 100, 'prepayment');
        } catch (HttpException $e) {
            $this->assertSame(422, $e->getStatusCode());
            $this->assertStringContainsString('already closed', $e->getMessage());

            throw $e;
        }
    }

    public function test_regular_and_partial_payments_are_unaffected_by_the_payoff_ceiling(): void
    {
        // The new payoff-amount ceiling only applies to type=prepayment —
        // a regular payment can still legitimately be up to the full
        // remaining balance (it doesn't claim to be "paying off early").
        $loan = $this->makeDisbursedLoan();
        $payoff = $loan->payoff_amount;
        $fullBalance = (float) $loan->remaining_balance;

        $this->assertGreaterThan($payoff, $fullBalance);

        // A regular payment for more than the payoff amount (but not more
        // than the full balance) should NOT be rejected and should NOT
        // trigger the early-payoff branch, since it isn't a prepayment.
        $this->recordPaymentViaController($loan, $payoff + 50, 'payment');
        $loan->refresh();

        $this->assertFalse($loan->paid_off_early);
    }
}
