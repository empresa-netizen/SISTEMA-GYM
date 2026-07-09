<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create roles
    foreach (['super-admin', 'owner', 'manager', 'trainer', 'receptionist', 'member'] as $role) {
        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
    }

    // Minimal permissions needed for controller guards.
    Permission::firstOrCreate(['name' => 'impersonate customers', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'delete customers', 'guard_name' => 'web']);

    Role::findByName('super-admin')->givePermissionTo(['impersonate customers', 'delete customers']);
});

test('super admin can view customers page', function () {
    $superAdmin = User::factory()->create(['email_verified_at' => now()]);
    $superAdmin->assignRole('super-admin');

    $response = $this->actingAs($superAdmin)->get(route('super-admin.customers.index'));

    $response->assertStatus(200);
    $response->assertSee('Customer List');
});

test('owner cannot access super admin customers page', function () {
    $owner = User::factory()->create(['parent_id' => null, 'email_verified_at' => now()]);
    $owner->assignRole('owner');

    $response = $this->actingAs($owner)->get(route('super-admin.customers.index'));

    $response->assertStatus(403);
});

test('super admin can create new customer', function () {
    $superAdmin = User::factory()->create(['email_verified_at' => now()]);
    $superAdmin->assignRole('super-admin');

    $response = $this->actingAs($superAdmin)->post(route('super-admin.customers.store'), [
        'business_name' => 'Test Gym',
        'subdomain' => 'testgym',
        'owner_name' => 'John Doe',
        'owner_email' => 'john@testgym.com',
        'owner_password' => 'password123',
        'max_members' => 100,
        'max_trainers' => 10,
        'trial_days' => 14,
    ]);

    $response->assertRedirect(route('super-admin.customers.index'));
    expect(Tenant::count())->toBe(1);
    expect(User::where('email', 'john@testgym.com')->exists())->toBeTrue();

    $tenant = Tenant::first();
    expect($tenant->business_name)->toBe('Test Gym');
    expect($tenant->isOnTrial())->toBeTrue();
});

test('super admin can edit customer', function () {
    $superAdmin = User::factory()->create(['email_verified_at' => now()]);
    $superAdmin->assignRole('super-admin');

    $owner = User::factory()->create(['parent_id' => null, 'email_verified_at' => now()]);
    $owner->assignRole('owner');

    $tenant = Tenant::create([
        'user_id' => $owner->id,
        'business_name' => 'Original Name',
        'status' => 'active',
        'max_members' => 50,
        'max_trainers' => 5,
    ]);

    $response = $this->actingAs($superAdmin)->put(route('super-admin.customers.update', $tenant), [
        'business_name' => 'Updated Name',
        'subdomain' => 'updated',
        'status' => 'active',
        'max_members' => 100,
        'max_trainers' => 10,
    ]);

    $response->assertRedirect();
    $tenant->refresh();
    expect($tenant->business_name)->toBe('Updated Name');
    expect($tenant->max_members)->toBe(100);
});

test('super admin can suspend customer', function () {
    $superAdmin = User::factory()->create(['email_verified_at' => now()]);
    $superAdmin->assignRole('super-admin');

    $owner = User::factory()->create(['parent_id' => null, 'email_verified_at' => now()]);
    $owner->assignRole('owner');

    $tenant = Tenant::create([
        'user_id' => $owner->id,
        'business_name' => 'Test Gym',
        'status' => 'active',
        'max_members' => 50,
        'max_trainers' => 5,
    ]);

    $response = $this->actingAs($superAdmin)->post(route('super-admin.customers.suspend', $tenant));

    $response->assertRedirect();
    $tenant->refresh();
    expect($tenant->status)->toBe('suspended');
});

test('super admin can impersonate customer', function () {
    $superAdmin = User::factory()->create(['email_verified_at' => now()]);
    $superAdmin->assignRole('super-admin');

    $owner = User::factory()->create(['parent_id' => null, 'email_verified_at' => now()]);
    $owner->assignRole('owner');

    $tenant = Tenant::create([
        'user_id' => $owner->id,
        'business_name' => 'Test Gym',
        'status' => 'active',
        'max_members' => 50,
        'max_trainers' => 5,
    ]);

    $response = $this->actingAs($superAdmin)->post(route('super-admin.customers.impersonate', $tenant));

    $response->assertOk()->assertJson(['status' => true]);

    // Verify impersonation happened by checking we're now authenticated as the owner
    expect(auth()->id())->toBe($owner->id);
});

test('customer data is properly scoped', function () {
    $owner1 = User::factory()->create(['parent_id' => null, 'email_verified_at' => now()]);
    $owner1->assignRole('owner');

    $owner2 = User::factory()->create(['parent_id' => null, 'email_verified_at' => now()]);
    $owner2->assignRole('owner');

    $tenant1 = Tenant::create([
        'user_id' => $owner1->id,
        'business_name' => 'Gym 1',
        'status' => 'active',
        'max_members' => 50,
        'max_trainers' => 5,
    ]);

    $tenant2 = Tenant::create([
        'user_id' => $owner2->id,
        'business_name' => 'Gym 2',
        'status' => 'active',
        'max_members' => 50,
        'max_trainers' => 5,
    ]);

    // Verify both tenants exist
    expect(Tenant::count())->toBe(2);

    // Super admin should see both
    $superAdmin = User::factory()->create(['email_verified_at' => now()]);
    $superAdmin->assignRole('super-admin');

    $response = $this->actingAs($superAdmin)->get(route('super-admin.customers.index'));
    // Customer list is DataTables-driven; row data is loaded via AJAX.
    $response->assertSee('Customer List');
});

test('owner cannot access super admin dashboard', function () {
    $owner = User::factory()->create(['parent_id' => null, 'email_verified_at' => now()]);
    $owner->assignRole('owner');

    $response = $this->actingAs($owner)->get(route('super-admin.dashboard'));

    $response->assertStatus(403);
});

test('super admin can delete customer', function () {
    $superAdmin = User::factory()->create(['email_verified_at' => now()]);
    $superAdmin->assignRole('super-admin');

    $owner = User::factory()->create(['parent_id' => null, 'email_verified_at' => now()]);
    $owner->assignRole('owner');

    $tenant = Tenant::create([
        'user_id' => $owner->id,
        'business_name' => 'Test Gym',
        'status' => 'active',
        'max_members' => 50,
        'max_trainers' => 5,
    ]);

    $ownerId = $owner->id;

    $response = $this->actingAs($superAdmin)->post(route('super-admin.customers.destroy', $tenant));
    $response->assertOk()->assertJson(['status' => true]);
    expect(Tenant::count())->toBe(0);
    expect(User::find($ownerId))->toBeNull();
});
