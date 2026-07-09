<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->owner->assignRole('owner');
});

test('owner can list users', function () {
    $user = User::factory()->create(['parent_id' => $this->owner->id]);

    $this->actingAs($this->owner)
        ->get(route('users.index'))
        ->assertOk()
        // Users index is rendered via DataTables (AJAX), so the initial HTML
        // may not contain row data.
        ->assertSee('Usuários');
});

test('owner can create user', function () {
    $role = Role::where('name', 'member')->first();

    $this->actingAs($this->owner)
        ->post(route('users.store'), [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',

            'role' => $role->name,
        ])
        ->assertRedirect(route('users.index'));

    $this->assertDatabaseHas('users', [
        'email' => 'new@example.com',
        'parent_id' => $this->owner->id,
    ]);
});

test('owner can update user', function () {
    $user = User::factory()->create(['parent_id' => $this->owner->id]);
    $role = Role::where('name', 'member')->first();

    $this->actingAs($this->owner)
        ->put(route('users.update', $user), [
            'name' => 'Updated Name',
            'email' => $user->email,

            'role' => $role->name,
        ])
        ->assertRedirect(route('users.index'));

    $this->assertEquals('Updated Name', $user->fresh()->name);
});

test('owner can delete user', function () {
    $user = User::factory()->create(['parent_id' => $this->owner->id]);

    $this->actingAs($this->owner)
        ->post(route('users.destroy', $user))
        ->assertOk()
        ->assertJson(['status' => true]);

    $this->assertModelMissing($user);
});

test('owner can view user details', function () {
    $user = User::factory()->create(['parent_id' => $this->owner->id]);

    $this->actingAs($this->owner)
        ->get(route('users.show', $user))
        ->assertOk()
        ->assertSee($user->name)
        ->assertSee($user->email);
});

test('owner cannot access users from other tenant', function () {
    $otherOwner = User::factory()->create();
    $otherOwner->assignRole('owner');
    $otherUser = User::factory()->create(['parent_id' => $otherOwner->id]);

    $this->actingAs($this->owner)
        ->get(route('users.show', $otherUser))
        ->assertForbidden();

    $this->actingAs($this->owner)
        ->get(route('users.edit', $otherUser))
        ->assertForbidden();

    $this->actingAs($this->owner)
        ->put(route('users.update', $otherUser), [
            'name' => 'Hacked',
            'email' => $otherUser->email,

            'role' => 'member',
        ])
        ->assertForbidden();

    $this->actingAs($this->owner)
        ->post(route('users.destroy', $otherUser))
        ->assertForbidden();
});
