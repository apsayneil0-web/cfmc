<?php

namespace Tests\Feature;

use App\Models\Machine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * "X archived. Undo" toast: archiving flashes an undo link to the matching
 * unarchive route, and following it restores the record and returns to the
 * page the user was on (not the archived list).
 */
class ArchiveUndoTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(int $roleId, string $status = 'active'): User
    {
        return User::create([
            'username' => 'user_'.uniqid(),
            'name' => 'Test User '.uniqid(),
            'password' => bcrypt('password'),
            'roleID' => $roleId,
            'status' => $status,
        ]);
    }

    public function test_archiving_a_machine_offers_undo_that_restores_it_in_place(): void
    {
        $manager = $this->makeUser(2);
        $machine = Machine::create([
            'name' => 'Undo Tractor',
            'type' => 'tractor',
            'quantity' => 1,
            'daily_hectare_limit' => 5,
        ]);

        $this->actingAs($manager)
            ->patch(route('manager.machinery.archive', $machine))
            ->assertRedirect(route('manager.machinery'))
            ->assertSessionHas('undo', fn ($undo) => $undo['url'] === route('manager.machinery.unarchive', $machine));

        $this->assertNotNull($machine->fresh()->archived_at);

        $this->actingAs($manager)
            ->from(route('manager.machinery'))
            ->patch(route('manager.machinery.unarchive', $machine), ['undo' => 1])
            ->assertRedirect(route('manager.machinery'))
            ->assertSessionHas('undo_done');

        $this->assertNull($machine->fresh()->archived_at);
    }

    public function test_plain_unarchive_still_goes_to_the_archived_list(): void
    {
        $manager = $this->makeUser(2);
        $machine = Machine::create([
            'name' => 'Archived Seeder',
            'type' => 'seeder',
            'quantity' => 1,
            'daily_hectare_limit' => 5,
            'archived_at' => now(),
        ]);

        $this->actingAs($manager)
            ->patch(route('manager.machinery.unarchive', $machine))
            ->assertRedirect(route('manager.machinery', ['archived' => 1]));
    }

    public function test_archiving_an_active_user_offers_undo(): void
    {
        $manager = $this->makeUser(2);
        $farmerAccount = $this->makeUser(3);

        $this->actingAs($manager)
            ->patch(route('user.archive', $farmerAccount))
            ->assertJson(['success' => true, 'undo' => true])
            ->assertSessionHas('undo');

        $this->actingAs($manager)
            ->from(route('manager.user-management'))
            ->patch(route('user.unarchive', $farmerAccount), ['undo' => 1])
            ->assertRedirect(route('manager.user-management'))
            ->assertSessionHas('undo_done');

        $this->assertSame('active', $farmerAccount->fresh()->status);
    }

    public function test_archiving_an_inactive_user_offers_no_undo(): void
    {
        // Unarchive always restores to "active", which would not undo this.
        $manager = $this->makeUser(2);
        $farmerAccount = $this->makeUser(3, 'inactive');

        $this->actingAs($manager)
            ->patch(route('user.archive', $farmerAccount))
            ->assertJson(['success' => true, 'undo' => false])
            ->assertSessionMissing('undo');
    }
}
