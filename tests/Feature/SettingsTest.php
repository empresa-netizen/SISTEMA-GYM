<?php

use App\Models\Setting;
use App\Models\User;

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->owner->assignRole('owner');
});

test('owner can list settings', function () {
    Setting::create([
        'name' => 'app_name',
        'value' => 'My Gym',
        'type' => 'app',
        'parent_id' => $this->owner->id,
    ]);

    $this->actingAs($this->owner)
        ->get(route('settings.index'))
        ->assertOk()
        ->assertSee('My Gym');
});

test('owner can open account settings hub', function () {
    $this->actingAs($this->owner)
        ->get(route('account.settings'))
        ->assertOk()
        ->assertSee('Configurações')
        ->assertSee('Identidade visual')
        ->assertSee('Logs de E-mail')
        ->assertSee('Central de Ajuda');
});

test('owner can update settings', function () {
    $this->actingAs($this->owner)
        ->post(route('settings.update'), [
            'app_name' => 'Updated Gym',
            'app_currency' => 'USD',
        ])
        ->assertRedirect(route('settings.index'));

    $this->assertDatabaseHas('settings', [
        'name' => 'app_name',
        'value' => 'Updated Gym',
        'parent_id' => $this->owner->id,
    ]);
});

test('owner can show setting', function () {
    $setting = Setting::create([
        'name' => 'app_name',
        'value' => 'My Gym',
        'type' => 'app',
        'parent_id' => $this->owner->id,
    ]);

    $this->actingAs($this->owner)
        ->get(route('settings.show', $setting->name))
        ->assertOk()
        ->assertJson([
            'name' => 'app_name',
            'value' => 'My Gym',
        ]);
});

test('owner cannot access settings from other tenant', function () {
    $otherOwner = User::factory()->create();
    $setting = Setting::create([
        'name' => 'app_name',
        'value' => 'Other Gym',
        'type' => 'app',
        'parent_id' => $otherOwner->id,
    ]);

    // Show should return 404 as it looks for setting with current parent_id
    $this->actingAs($this->owner)
        ->get(route('settings.show', 'app_name'))
        ->assertNotFound();

    // Update should create NEW setting for current user, NOT update other user's setting
    $this->actingAs($this->owner)
        ->post(route('settings.update'), [
            'app_name' => 'My Value',
        ]);

    $this->assertDatabaseHas('settings', [
        'name' => 'app_name',
        'value' => 'Other Gym',
        'parent_id' => $otherOwner->id,
    ]);

    $this->assertDatabaseHas('settings', [
        'name' => 'app_name',
        'value' => 'My Value',
        'parent_id' => $this->owner->id,
    ]);
});
