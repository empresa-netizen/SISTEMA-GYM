<?php

use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->owner->assignRole('owner');

    $this->plan = MembershipPlan::create([
        'name' => 'Gold Plan',
        'duration' => 30, // days
        'price' => 100,
        'parent_id' => $this->owner->id,
    ]);
});

test('owner can list members', function () {
    $member = Member::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '1234567890',
        'status' => 'active',
        'membership_plan_id' => $this->plan->id,
        'parent_id' => $this->owner->id,
    ]);

    $this->actingAs($this->owner)
        ->get(route('members.index'))
        ->assertOk()
        // Members index is rendered via DataTables (AJAX), so the initial HTML
        // may not contain row data.
        ->assertSee('Clientes');
});

test('owner can create member with photo and plan', function () {
    Storage::fake('public');
    // Avoid GD dependency in test container.
    $photo = UploadedFile::fake()->create('member.jpg', 10, 'image/jpeg');

    $this->actingAs($this->owner)
        ->post(route('members.store'), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '0987654321',
            'status' => 'active',
            'membership_plan_id' => $this->plan->id,
            'membership_start_date' => now()->format('Y-m-d'),
            'photo' => $photo,
            'gender' => 'female',
            'address' => '123 St',
        ])
        ->assertRedirect(route('members.index'));

    $this->assertDatabaseHas('members', [
        'email' => 'jane@example.com',
        'parent_id' => $this->owner->id,
    ]);

    $member = Member::where('email', 'jane@example.com')->first();
    $this->assertNotNull($member->member_id);
    $this->assertNotNull($member->membership_end_date);
    Storage::disk('public')->assertExists($member->photo);
});

test('owner can update member', function () {
    $member = Member::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'parent_id' => $this->owner->id,
        'status' => 'active',
    ]);

    $this->actingAs($this->owner)
        ->put(route('members.update', $member), [
            'name' => 'John Updated',
            'email' => 'john@example.com',
            'status' => 'inactive',
            'gender' => 'male',
            'membership_plan_id' => $this->plan->id,
            'membership_start_date' => now()->format('Y-m-d'),
        ])
        ->assertRedirect(route('members.index'));

    $this->assertEquals('John Updated', $member->fresh()->name);
    $this->assertEquals('inactive', $member->fresh()->status);
});

test('owner can delete member', function () {
    $member = Member::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'parent_id' => $this->owner->id,
        'status' => 'active',
    ]);

    $this->actingAs($this->owner)
        ->post(route('members.destroy', $member))
        ->assertOk()
        ->assertJson(['status' => true]);

    $this->assertModelMissing($member);
});

test('owner cannot access members from other tenant', function () {
    $otherOwner = User::factory()->create();
    $otherMember = Member::create([
        'name' => 'Other Member',
        'email' => 'other@example.com',
        'parent_id' => $otherOwner->id,
        'status' => 'active',
    ]);

    $this->actingAs($this->owner)
        ->get(route('members.show', $otherMember))
        // Tenant scoping returns 404 to avoid leaking resource existence.
        ->assertNotFound();

    $this->actingAs($this->owner)
        ->put(route('members.update', $otherMember), [
            'name' => 'Hacked',
            'email' => 'hacked@member.com',
            'phone' => '1234567890',
            'status' => 'active',
            'membership_plan_id' => $this->plan->id,
            'membership_start_date' => now()->format('Y-m-d'),
            'gender' => 'male',
            'address' => '123 Hack St',
        ])
        ->assertNotFound();

    $this->actingAs($this->owner)
        ->post(route('members.destroy', $otherMember))
        ->assertNotFound();
});
