<?php

use App\Models\PlatformSubscriptionTier;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create roles
    foreach (['super-admin', 'owner', 'manager', 'trainer', 'receptionist', 'member'] as $role) {
        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
    }
});

test('super admin can view platform subscription tiers page', function () {
    $superAdmin = User::factory()->create(['email_verified_at' => now()]);
    $superAdmin->assignRole('super-admin');

    $tier = PlatformSubscriptionTier::factory()->create();

    $response = $this->actingAs($superAdmin)->get(route('super-admin.platform-subscriptions.index'));

    $response->assertStatus(200);
    $response->assertSee('Platform Subscription Tiers');
    // This page is DataTables-driven; row data is loaded via AJAX.
});

test('owner cannot access platform subscription tiers page', function () {
    $owner = User::factory()->create(['parent_id' => null, 'email_verified_at' => now()]);
    $owner->assignRole('owner');

    $response = $this->actingAs($owner)->get(route('super-admin.platform-subscriptions.index'));

    $response->assertStatus(403);
});

test('super admin can view create tier page', function () {
    $superAdmin = User::factory()->create(['email_verified_at' => now()]);
    $superAdmin->assignRole('super-admin');

    $response = $this->actingAs($superAdmin)->get(route('super-admin.platform-subscriptions.create'));

    $response->assertStatus(200);
    $response->assertSee('Create Tier');
});

test('super admin can create new platform subscription tier', function () {
    $superAdmin = User::factory()->create(['email_verified_at' => now()]);
    $superAdmin->assignRole('super-admin');

    $response = $this->actingAs($superAdmin)->post(route('super-admin.platform-subscriptions.store'), [
        'name' => 'Premium Plan',
        'description' => 'Premium tier for large gyms',
        'price' => 99.99,
        'interval' => 'monthly',
        'trial_days' => 30,
        'max_members_per_tenant' => 500,
        'max_trainers_per_tenant' => 50,
        'max_staff_per_tenant' => 20,
        'is_active' => true,
        'is_featured' => false,
        'sort_order' => 2,
    ]);

    $response->assertRedirect(route('super-admin.platform-subscriptions.index'));
    $response->assertSessionHas('success');

    expect(PlatformSubscriptionTier::count())->toBe(1);

    $tier = PlatformSubscriptionTier::first();
    expect($tier->name)->toBe('Premium Plan');
    expect($tier->price)->toBe('99.99');
    expect($tier->max_members_per_tenant)->toBe(500);
});

test('super admin can view tier details', function () {
    $superAdmin = User::factory()->create(['email_verified_at' => now()]);
    $superAdmin->assignRole('super-admin');

    $tier = PlatformSubscriptionTier::factory()->create(['name' => 'Gold Plan']);

    $response = $this->actingAs($superAdmin)->get(route('super-admin.platform-subscriptions.show', $tier));

    $response->assertStatus(200);
    $response->assertSee('Gold Plan');
});

test('super admin can edit platform subscription tier', function () {
    $superAdmin = User::factory()->create(['email_verified_at' => now()]);
    $superAdmin->assignRole('super-admin');

    $tier = PlatformSubscriptionTier::factory()->create([
        'name' => 'Basic Plan',
        'price' => 29.99,
    ]);

    $response = $this->actingAs($superAdmin)->put(route('super-admin.platform-subscriptions.update', $tier), [
        'name' => 'Updated Basic Plan',
        'description' => 'Updated description',
        'price' => 39.99,
        'interval' => 'monthly',
        'trial_days' => 14,
        'max_members_per_tenant' => 100,
        'max_trainers_per_tenant' => 10,
        'max_staff_per_tenant' => 5,
        'is_active' => true,
        'is_featured' => false,
        'sort_order' => 1,
    ]);

    $response->assertRedirect(route('super-admin.platform-subscriptions.show', $tier));
    $response->assertSessionHas('success');

    $tier->refresh();
    expect($tier->name)->toBe('Updated Basic Plan');
    expect($tier->price)->toBe('39.99');
});

test('super admin can delete tier without customers', function () {
    $superAdmin = User::factory()->create(['email_verified_at' => now()]);
    $superAdmin->assignRole('super-admin');

    $tier = PlatformSubscriptionTier::factory()->create();

    $response = $this->actingAs($superAdmin)->post(route('super-admin.platform-subscriptions.destroy', $tier));
    $response->assertOk()->assertJson(['status' => true]);

    expect(PlatformSubscriptionTier::count())->toBe(0);
});

test('super admin cannot delete tier with active customers', function () {
    $superAdmin = User::factory()->create(['email_verified_at' => now()]);
    $superAdmin->assignRole('super-admin');

    $tier = PlatformSubscriptionTier::factory()->create();

    // Create tenant/customer with this tier
    $owner = User::factory()->create(['parent_id' => null]);
    $owner->assignRole('owner');

    Tenant::create([
        'user_id' => $owner->id,
        'business_name' => 'Test Gym',
        'subdomain' => 'testgym',
        'status' => 'active',
        'platform_subscription_tier_id' => $tier->id,
    ]);

    $response = $this->actingAs($superAdmin)->post(route('super-admin.platform-subscriptions.destroy', $tier));
    $response->assertOk()->assertJson(['status' => false]);

    expect(PlatformSubscriptionTier::count())->toBe(1);
});

test('tier name must be unique', function () {
    $superAdmin = User::factory()->create(['email_verified_at' => now()]);
    $superAdmin->assignRole('super-admin');

    PlatformSubscriptionTier::factory()->create(['name' => 'Starter Plan']);

    $response = $this->actingAs($superAdmin)->post(route('super-admin.platform-subscriptions.store'), [
        'name' => 'Starter Plan',
        'price' => 29.99,
        'interval' => 'monthly',
        'is_active' => true,
    ]);

    $response->assertSessionHasErrors('name');
});

test('tier price is required and must be numeric', function () {
    $superAdmin = User::factory()->create(['email_verified_at' => now()]);
    $superAdmin->assignRole('super-admin');

    $response = $this->actingAs($superAdmin)->post(route('super-admin.platform-subscriptions.store'), [
        'name' => 'Test Plan',
        'price' => 'invalid',
        'interval' => 'monthly',
    ]);

    $response->assertSessionHasErrors('price');
});

test('auto-generates slug from tier name', function () {
    $superAdmin = User::factory()->create(['email_verified_at' => now()]);
    $superAdmin->assignRole('super-admin');

    $this->actingAs($superAdmin)->post(route('super-admin.platform-subscriptions.store'), [
        'name' => 'Enterprise Plan',
        'price' => 199.99,
        'interval' => 'monthly',
        'is_active' => true,
    ]);

    $tier = PlatformSubscriptionTier::first();
    expect($tier->slug)->toBe('enterprise-plan');
});

test('counts tenants correctly', function () {
    $tier = PlatformSubscriptionTier::factory()->create();

    // Create 2 tenants with this tier
    $owner1 = User::factory()->create(['parent_id' => null]);
    $owner1->assignRole('owner');
    $owner2 = User::factory()->create(['parent_id' => null]);
    $owner2->assignRole('owner');

    Tenant::create([
        'user_id' => $owner1->id,
        'business_name' => 'Gym 1',
        'subdomain' => 'gym1',
        'status' => 'active',
        'platform_subscription_tier_id' => $tier->id,
    ]);

    Tenant::create([
        'user_id' => $owner2->id,
        'business_name' => 'Gym 2',
        'subdomain' => 'gym2',
        'status' => 'active',
        'platform_subscription_tier_id' => $tier->id,
    ]);

    expect($tier->tenants()->count())->toBe(2);
});
