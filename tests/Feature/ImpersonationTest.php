<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ImpersonationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create roles if they don't exist
        if (! Role::where('name', 'super-admin')->exists()) {
            Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
        }
    }

    public function test_admin_can_impersonate_user()
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $user = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('impersonate', $user->id))
            ->assertRedirect(); // Usually redirects to home

        $this->assertTrue(auth()->id() === $user->id);
        $this->assertTrue(app('impersonate')->isImpersonating());
    }

    public function test_admin_can_leave_impersonation()
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $user = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('impersonate', $user->id));

        $this->get(route('impersonate.leave'))
            ->assertRedirect();

        $this->assertTrue(auth()->id() === $admin->id);
        $this->assertFalse(app('impersonate')->isImpersonating());
    }

    public function test_non_admin_cannot_impersonate()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $response = $this->actingAs($user1)
            ->get(route('impersonate', $user2->id));

        // The package might return 403 or 404 depending on configuration
        // But since we defined canImpersonate() to return false for members, it should be forbidden.
        $response->assertStatus(403);
    }
}
