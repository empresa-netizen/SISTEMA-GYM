<?php

namespace Tests\Feature;

use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MembershipPlanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_owner_can_list_membership_plans()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $plan = MembershipPlan::create([
            'parent_id' => $owner->id,
            'name' => 'Test Plan',
            'price' => 100,
            'duration_type' => 'monthly',
            'duration_value' => 1,
        ]);

        $this->actingAs($owner)
            ->get(route('membership-plans.index'))
            ->assertOk()
            ->assertSee('Meus produtos')
            ->assertSee('Test Plan');
    }

    public function test_owner_can_create_membership_plan()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $this->actingAs($owner)
            ->post(route('membership-plans.store'), [
                'name' => 'New Plan',
                'price' => 200,
                'duration_type' => 'yearly',
                'duration_value' => 1,
                'is_active' => true,
                'personal_training' => false,
            ])
            ->assertRedirect(route('membership-plans.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('membership_plans', [
            'name' => 'New Plan',
            'parent_id' => $owner->id,
        ]);
    }

    public function test_owner_can_update_membership_plan()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $plan = MembershipPlan::create([
            'parent_id' => $owner->id,
            'name' => 'Old Plan',
            'price' => 100,
            'duration_type' => 'monthly',
            'duration_value' => 1,
        ]);

        $this->actingAs($owner)
            ->put(route('membership-plans.update', $plan), [
                'name' => 'Updated Plan',
                'price' => 150,
                'duration_type' => 'monthly',
                'duration_value' => 1,
                'is_active' => true,
                'personal_training' => true,
            ])
            ->assertRedirect(route('membership-plans.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('membership_plans', [
            'id' => $plan->id,
            'name' => 'Updated Plan',
            'price' => 150,
            'personal_training' => true,
        ]);
    }

    public function test_owner_can_delete_membership_plan()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $plan = MembershipPlan::create([
            'parent_id' => $owner->id,
            'name' => 'Delete Me',
            'price' => 100,
            'duration_type' => 'monthly',
            'duration_value' => 1,
        ]);

        $this->actingAs($owner)
            ->post(route('membership-plans.destroy', $plan))
            ->assertOk()
            ->assertJson(['status' => true]);

        $this->assertDatabaseMissing('membership_plans', [
            'id' => $plan->id,
        ]);
    }

    public function test_owner_cannot_access_other_tenant_plans()
    {
        $owner1 = User::factory()->create();
        $owner1->assignRole('owner');

        $owner2 = User::factory()->create();
        $owner2->assignRole('owner');

        $plan = MembershipPlan::create([
            'parent_id' => $owner2->id,
            'name' => 'Owner 2 Plan',
            'price' => 100,
            'duration_type' => 'monthly',
            'duration_value' => 1,
        ]);

        $this->actingAs($owner1)
            ->get(route('membership-plans.edit', $plan))
            ->assertNotFound();

        $this->actingAs($owner1)
            ->put(route('membership-plans.update', $plan), [
                'name' => 'Hacked',
                'price' => 0,
                'duration_type' => 'monthly',
                'duration_value' => 1,
            ])
            ->assertNotFound();

        $this->actingAs($owner1)
            ->post(route('membership-plans.destroy', $plan))
            ->assertNotFound();
    }

    public function test_owner_can_create_inactive_membership_plan()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $this->actingAs($owner)
            ->post(route('membership-plans.store'), [
                'name' => 'Inactive Plan',
                'price' => 150,
                'duration_type' => 'monthly',
                'duration_value' => 1,
                'is_active' => false,
                'personal_training' => false,
            ])
            ->assertRedirect(route('membership-plans.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('membership_plans', [
            'name' => 'Inactive Plan',
            'parent_id' => $owner->id,
            'is_active' => false,
        ]);
    }
}
