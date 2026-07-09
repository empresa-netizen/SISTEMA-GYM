<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PermissionSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_owner_can_filter_users_by_role()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $trainer = User::factory()->create(['name' => 'John Trainer', 'parent_id' => $owner->id]);
        $trainer->assignRole('trainer');

        $member = User::factory()->create(['name' => 'Jane Member', 'parent_id' => $owner->id]);
        $member->assignRole('member');

        $this->actingAs($owner)
            ->get(route('users.index', ['role' => 'trainer']))
            ->assertOk()
            // Users list is DataTables-driven; filtering happens on the AJAX endpoint.
            ->assertSee('Usuários');

        $this->actingAs($owner)
            ->get(route('users.index', ['role' => 'member']))
            ->assertOk()
            ->assertSee('Usuários');
    }

    public function test_owner_can_search_users()
    {
        $owner = User::factory()->create();
        $owner->assignRole('owner');

        $user1 = User::factory()->create(['name' => 'UniqueName123', 'email' => 'unique1@example.com', 'parent_id' => $owner->id]);
        $user2 = User::factory()->create(['name' => 'OtherUser', 'email' => 'other@example.com', 'parent_id' => $owner->id]);

        $this->actingAs($owner)
            ->get(route('users.index', ['search' => 'UniqueName123']))
            ->assertOk()
            ->assertSee('Usuários');

        $this->actingAs($owner)
            ->get(route('users.index', ['search' => 'unique1@example.com']))
            ->assertOk()
            ->assertSee('Usuários');
    }
}
