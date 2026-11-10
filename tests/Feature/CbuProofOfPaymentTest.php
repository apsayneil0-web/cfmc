<?php

namespace Tests\Feature;

use App\Models\Cbu;
use App\Models\CbuTransaction;
use App\Models\Farmer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Every CBU contribution must carry a proof of payment (receipt photo or
 * deposit slip) — recorded via Manager\PaymentController::recordCbuPayment.
 */
class CbuProofOfPaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    private Farmer $farmer;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->manager = User::create([
            'username' => 'manager_'.uniqid(),
            'name' => 'Test Manager',
            'password' => bcrypt('password'),
            'roleID' => 2,
            'status' => 'active',
        ]);

        $this->farmer = Farmer::create([
            'first_name' => 'Test',
            'last_name' => 'Farmer',
            'contact_number' => '09123456789',
            'land_area' => 1,
            'province' => 'South Cotabato',
            'municipality' => 'Surallah',
            'status' => 'approved',
        ]);
    }

    private function pay(array $overrides = [])
    {
        return $this->actingAs($this->manager)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
            ->from(route('manager.payment'))
            ->post(route('manager.payment.record-cbu'), array_merge([
                'farmer_id' => $this->farmer->id,
                'type' => 'contribution',
                'amount' => 4000,
            ], $overrides));
    }

    public function test_contribution_without_proof_is_rejected(): void
    {
        $this->pay()->assertSessionHasErrors('proof_document');

        $this->assertSame(0, CbuTransaction::count());
    }

    public function test_contribution_with_proof_is_recorded_and_file_stored(): void
    {
        $this->pay(['proof_document' => UploadedFile::fake()->image('receipt.jpg')])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('manager.payment'));

        $transaction = CbuTransaction::sole();

        $this->assertNotNull($transaction->proof_document);
        $this->assertStringStartsWith('cbu_proofs/', $transaction->proof_document);
        Storage::disk('public')->assertExists($transaction->proof_document);
        $this->assertEquals(4000, (float) Cbu::where('farmer_id', $this->farmer->id)->value('balance'));
    }

    public function test_pdf_proof_is_accepted(): void
    {
        $this->pay(['proof_document' => UploadedFile::fake()->create('slip.pdf', 300, 'application/pdf')])
            ->assertSessionHasNoErrors();

        $this->assertSame(1, CbuTransaction::count());
    }

    public function test_wrong_file_type_is_rejected(): void
    {
        $this->pay(['proof_document' => UploadedFile::fake()->create('note.docx', 20, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')])
            ->assertSessionHasErrors('proof_document');

        $this->assertSame(0, CbuTransaction::count());
    }

    public function test_file_over_5mb_is_rejected(): void
    {
        $this->pay(['proof_document' => UploadedFile::fake()->image('big.jpg')->size(6000)])
            ->assertSessionHasErrors('proof_document');

        $this->assertSame(0, CbuTransaction::count());
    }

    public function test_rejected_amount_does_not_leave_a_stored_file(): void
    {
        // Below the ₱4,000 first-contribution minimum → aborts with 422.
        $this->pay(['amount' => 500, 'proof_document' => UploadedFile::fake()->image('receipt.jpg')])
            ->assertStatus(422);

        $this->assertSame([], Storage::disk('public')->allFiles('cbu_proofs'));
    }

    public function test_manager_can_replace_proof_when_editing_entry(): void
    {
        $this->pay(['proof_document' => UploadedFile::fake()->image('first.jpg')]);
        $transaction = CbuTransaction::sole();
        $oldPath = $transaction->proof_document;

        $this->actingAs($this->manager)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
            ->put(route('manager.cbu.update', $transaction), [
                'type' => 'contribution',
                'amount' => 4000,
                'transaction_date' => $transaction->transaction_date->toDateString(),
                'proof_document' => UploadedFile::fake()->image('second.png'),
            ])->assertSessionHasNoErrors();

        $newPath = $transaction->fresh()->proof_document;
        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_editing_without_new_file_keeps_existing_proof(): void
    {
        $this->pay(['proof_document' => UploadedFile::fake()->image('first.jpg')]);
        $transaction = CbuTransaction::sole();
        $path = $transaction->proof_document;

        $this->actingAs($this->manager)
            ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
            ->put(route('manager.cbu.update', $transaction), [
                'type' => 'contribution',
                'amount' => 4500,
                'transaction_date' => $transaction->transaction_date->toDateString(),
            ])->assertSessionHasNoErrors();

        $this->assertSame($path, $transaction->fresh()->proof_document);
    }
}
