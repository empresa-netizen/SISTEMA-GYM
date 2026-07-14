<?php

use App\Models\User;
use Database\Seeders\MgteamUserSeeder;
use Illuminate\Support\Facades\Hash;

it('seeds the canonical admin with a working password', function () {
    $this->seed(MgteamUserSeeder::class);

    $admin = User::query()->where('email', 'coach@mgteam.app')->first();

    expect($admin)->not->toBeNull()
        ->and(Hash::check('password', $admin->password))->toBeTrue()
        ->and($admin->email_verified_at)->not->toBeNull()
        ->and($admin->twofa_enabled)->toBeFalse()
        ->and($admin->hasRole('owner'))->toBeTrue();
});

it('logs into the web dashboard with the legacy admin email alias', function () {
    $this->seed(MgteamUserSeeder::class);

    $admin = User::query()->where('email', 'coach@mgteam.app')->firstOrFail();

    $response = $this->post('/login', [
        'email' => 'admin@mgteam.app',
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($admin);
    $response->assertRedirect('/dashboard');
});

it('logs into API V1 with the legacy admin email alias', function () {
    $this->seed(MgteamUserSeeder::class);

    $admin = User::query()->where('email', 'coach@mgteam.app')->firstOrFail();

    $this->postJson('/api/v1/login', [
        'email' => 'admin@mgteam.app',
        'password' => 'password',
        'device_name' => 'pest-admin-alias',
    ])
        ->assertOk()
        ->assertJsonPath('user.id', $admin->id)
        ->assertJsonPath('user.email', 'coach@mgteam.app')
        ->assertJsonPath('token_type', 'Bearer');
});

it('logs into the mobile professional API with the legacy admin email alias', function () {
    $this->seed(MgteamUserSeeder::class);

    $admin = User::query()->where('email', 'coach@mgteam.app')->firstOrFail();

    $this->postJson('/api/auth/professional/login', [
        'email' => 'admin@mgteam.app',
        'password' => 'password',
        'device_name' => 'pest-mobile-admin-alias',
    ])
        ->assertOk()
        ->assertJsonPath('user.id', (string) $admin->id)
        ->assertJsonPath('user.email', 'coach@mgteam.app')
        ->assertJsonPath('user.role', 'ADMIN')
        ->assertJsonStructure(['token']);
});
