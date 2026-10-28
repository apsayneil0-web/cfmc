<?php

namespace Tests\Feature;

use App\Models\Cbu;
use App\Models\Farmer;
use App\Models\LoanBatch;
use App\Models\LoanRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Manage Batch (Loan Requests → Batch requests): each member row has an Edit
 * button and a "Remove from batch" button. Both open a modal by id, so the
 * modals must be on the page, and the forms behind them must work.
 */
class ManageBatchTest extends TestCase
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

    private function makeBatchMember(LoanBatch $batch, User $manager, float $amount = 10000): LoanRequest
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
            'last_name' => 'Farmer'.uniqid(),
            'contact_number' => '09123456789',
            'land_area' => 1,
            'province' => 'South Cotabato',
            'municipality' => 'Surallah',
            'status' => 'approved',
            'account_user_id' => $account->id,
        ]);
        Cbu::create(['farmer_id' => $farmer->id, 'balance' => 8000, 'status' => 'active']);

        return LoanRequest::create([
            'farmer_id' => $farmer->id,
            'type' => 'batch',
            'batch_id' => $batch->id,
            'requested_amount' => $amount,
            'purpose' => 'Seeds & Fertilizer',
            'repayment_terms_months' => 6,
            'collateral' => 'Carabao',
            'status' => 'pending',
            'requested_by' => $manager->id,
        ]);
    }

    public function test_manage_batch_renders_edit_and_remove_modals_even_when_the_list_is_filtered(): void
    {
        $manager = $this->makeManager();
        $batch = LoanBatch::firstOrCreate(['batch_number' => 1], ['capacity' => 10]);
        $member = $this->makeBatchMember($batch, $manager);

        // A status filter that excludes pending requests must not drop the
        // Edit modal the Manage Batch window points at.
        $this->actingAs($manager)
            ->get(route('manager.loan-request', ['mode' => 'batch', 'status' => 'approved']))
            ->assertOk()
            ->assertSee('id="manageBatchModal'.$batch->id.'"', false)
            ->assertSee("'editModal{$member->id}'", false)
            ->assertSee('id="editModal'.$member->id.'"', false)
            ->assertSee("'removeMemberModal{$member->id}'", false)
            ->assertSee('id="removeMemberModal'.$member->id.'"', false);
    }

    public function test_edit_updates_the_batch_member(): void
    {
        $manager = $this->makeManager();
        $batch = LoanBatch::firstOrCreate(['batch_number' => 1], ['capacity' => 10]);
        $member = $this->makeBatchMember($batch, $manager, 10000);

        $this->actingAs($manager)
            ->put(route('manager.loan-request.update', $member), [
                'farmer_id' => $member->farmer_id,
                'type' => 'batch',
                'batch_id' => $batch->id,
                'requested_amount' => 12000,
                'purpose' => 'Working Capital',
                'repayment_terms_months' => 12,
                'collateral' => 'Carabao',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('manager.loan-request'));

        $member->refresh();
        $this->assertEquals(12000, (float) $member->requested_amount);
        $this->assertSame('Working Capital', $member->purpose);
        $this->assertSame(12, (int) $member->repayment_terms_months);
    }

    public function test_remove_from_batch_frees_the_slot(): void
    {
        $manager = $this->makeManager();
        $batch = LoanBatch::firstOrCreate(['batch_number' => 1], ['capacity' => 10]);
        $member = $this->makeBatchMember($batch, $manager);

        $this->assertSame(1, $batch->fresh()->member_count);

        $this->actingAs($manager)
            ->patch(route('manager.loan-request.remove-batch-member', $member))
            ->assertRedirect(route('manager.loan-request'));

        $this->assertNotNull($member->fresh()->archived_at);
        $this->assertSame(0, $batch->fresh()->member_count);
    }
}
