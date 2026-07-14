<?php

use App\Models\MembershipPlan;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create roles
    foreach (['super-admin', 'owner', 'manager', 'trainer', 'receptionist', 'member'] as $role) {
        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
    }
});

/*
|--------------------------------------------------------------------------
| UserDataTable Tests
|--------------------------------------------------------------------------
| These tests verify the UserDataTable page loads correctly with proper
| structure, authorization, and export functionality.
*/

describe('UserDataTable', function () {
    beforeEach(function () {
        $this->owner = User::factory()->create(['email_verified_at' => now()]);
        $this->owner->assignRole('owner');
    });

    test('loads user listing page with datatable structure', function () {
        $this->actingAs($this->owner)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('user-table')
            ->assertSee('datatables-basic');
    });

    test('datatable has export buttons', function () {
        $this->actingAs($this->owner)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('Copy')
            ->assertSee('Excel');
    });

    test('unauthenticated user cannot access user datatable', function () {
        $this->get(route('users.index'))
            ->assertRedirect(route('login'));
    });
});

/*
|--------------------------------------------------------------------------
| CustomerDataTable Tests (Super Admin)
|--------------------------------------------------------------------------
| These tests verify the CustomerDataTable page loads correctly for super
| admins and is properly restricted from regular users.
*/

describe('CustomerDataTable', function () {
    beforeEach(function () {
        $this->superAdmin = User::factory()->create(['email_verified_at' => now()]);
        $this->superAdmin->assignRole('super-admin');

        $this->owner = User::factory()->create(['email_verified_at' => now()]);
        $this->owner->assignRole('owner');
    });

    test('super admin can load customer listing page with datatable', function () {
        $this->actingAs($this->superAdmin)
            ->get(route('super-admin.customers.index'))
            ->assertOk()
            ->assertSee('customer-table')
            ->assertSee('datatables-basic');
    });

    test('owner cannot access customer datatable', function () {
        $this->actingAs($this->owner)
            ->get(route('super-admin.customers.index'))
            ->assertForbidden();
    });

    test('datatable has export buttons', function () {
        $this->actingAs($this->superAdmin)
            ->get(route('super-admin.customers.index'))
            ->assertOk()
            ->assertSee('Copy')
            ->assertSee('Excel');
    });
});

/*
|--------------------------------------------------------------------------
| ProductDataTable Tests
|--------------------------------------------------------------------------
| These tests verify the ProductDataTable page loads correctly with proper
| structure and export functionality.
*/

describe('ProductDataTable', function () {
    beforeEach(function () {
        $this->owner = User::factory()->create(['email_verified_at' => now()]);
        $this->owner->assignRole('owner');
    });

    test('loads product listing page with datatable structure', function () {
        $this->actingAs($this->owner)
            ->get(route('products.index'))
            ->assertOk()
            ->assertSee('product-table')
            ->assertSee('datatables-basic');
    });

    test('datatable has export buttons', function () {
        $this->actingAs($this->owner)
            ->get(route('products.index'))
            ->assertOk()
            ->assertSee('Copy')
            ->assertSee('Excel');
    });

    test('unauthenticated user cannot access product datatable', function () {
        $this->get(route('products.index'))
            ->assertRedirect(route('login'));
    });
});

/*
|--------------------------------------------------------------------------
| CategoryDataTable Tests
|--------------------------------------------------------------------------
| These tests verify the CategoryDataTable page loads correctly with proper
| structure and export functionality.
*/

describe('CategoryDataTable', function () {
    beforeEach(function () {
        $this->owner = User::factory()->create(['email_verified_at' => now()]);
        $this->owner->assignRole('owner');
    });

    test('loads category listing page with datatable structure', function () {
        $this->actingAs($this->owner)
            ->get(route('categories.index'))
            ->assertOk()
            ->assertSee('category-table')
            ->assertSee('datatables-basic');
    });

    test('datatable has export buttons', function () {
        $this->actingAs($this->owner)
            ->get(route('categories.index'))
            ->assertOk()
            ->assertSee('Copy')
            ->assertSee('Excel');
    });

    test('unauthenticated user cannot access category datatable', function () {
        $this->get(route('categories.index'))
            ->assertRedirect(route('login'));
    });
});

/*
|--------------------------------------------------------------------------
| MembershipPlan Index Tests
|--------------------------------------------------------------------------
| These tests verify the membership plans listing page loads correctly with
| the MGTEAM card UI (no Yajra DataTable).
*/

describe('MembershipPlanIndex', function () {
    beforeEach(function () {
        $this->owner = User::factory()->create(['email_verified_at' => now()]);
        $this->owner->assignRole('owner');
    });

    test('loads membership plan listing page with MGTEAM cards', function () {
        MembershipPlan::create([
            'parent_id' => $this->owner->id,
            'name' => 'MGTEAM Plan',
            'price' => 199,
            'duration_type' => 'monthly',
            'duration_value' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($this->owner)
            ->get(route('membership-plans.index'))
            ->assertOk()
            ->assertSee('Meus produtos')
            ->assertSee('MGTEAM Plan')
            ->assertSee('mg-product-card');
    });

    test('unauthenticated user cannot access membership plan index', function () {
        $this->get(route('membership-plans.index'))
            ->assertRedirect(route('login'));
    });
});

/*
|--------------------------------------------------------------------------
| PlatformSubscriptionDataTable Tests (Super Admin)
|--------------------------------------------------------------------------
| These tests verify the PlatformSubscriptionDataTable page loads correctly
| for super admins and is properly restricted from regular users.
*/

describe('PlatformSubscriptionDataTable', function () {
    beforeEach(function () {
        $this->superAdmin = User::factory()->create(['email_verified_at' => now()]);
        $this->superAdmin->assignRole('super-admin');

        $this->owner = User::factory()->create(['email_verified_at' => now()]);
        $this->owner->assignRole('owner');
    });

    test('super admin can load platform subscription listing page with datatable', function () {
        $this->actingAs($this->superAdmin)
            ->get(route('super-admin.platform-subscriptions.index'))
            ->assertOk()
            ->assertSee('subscription-table')
            ->assertSee('datatables-basic');
    });

    test('owner cannot access platform subscription datatable', function () {
        $this->actingAs($this->owner)
            ->get(route('super-admin.platform-subscriptions.index'))
            ->assertForbidden();
    });

    test('datatable has export buttons', function () {
        $this->actingAs($this->superAdmin)
            ->get(route('super-admin.platform-subscriptions.index'))
            ->assertOk()
            ->assertSee('Copy')
            ->assertSee('Excel');
    });
});
