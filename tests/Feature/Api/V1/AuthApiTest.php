<?php

use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

describe('API V1 Auth', function () {
    it('returns 401 when accessing a protected route without a token', function () {
        $this->getJson('/api/v1/members')
            ->assertUnauthorized()
            ->assertJsonPath('error', 'unauthenticated');
    });

    it('rejects invalid credentials with 401', function () {
        createOwner(['email' => 'coach@test.app']);

        $this->postJson('/api/v1/login', [
            'email' => 'coach@test.app',
            'password' => 'wrong-password',
        ])
            ->assertUnauthorized()
            ->assertJsonStructure(['message', 'errors']);
    });

    it('rejects incomplete login payload with 422', function () {
        $this->postJson('/api/v1/login', [
            'email' => 'not-an-email',
        ])
            ->assertUnprocessable()
            ->assertJsonStructure(['message', 'errors']);
    });

    it('logs in successfully and returns Bearer token + UserResource', function () {
        $owner = createOwner([
            'email' => 'coach@test.app',
            'name' => 'Coach Test',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'coach@test.app',
            'password' => 'password',
            'device_name' => 'pest-suite',
        ]);

        $response->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonStructure([
                'message',
                'token_type',
                'access_token',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'parent_id',
                    'tenant_id',
                    'avatar',
                    'roles',
                    'created_at',
                ],
            ])
            ->assertJsonPath('user.id', $owner->id)
            ->assertJsonPath('user.email', 'coach@test.app');

        expect($response->json('access_token'))->not->toBeEmpty();
    });

    it('logs in a student member through the V1 login endpoint', function () {
        $owner = createOwner([
            'email' => 'coach-student-login@test.app',
            'name' => 'Coach Student Login',
            'password' => Hash::make('password'),
        ]);
        $member = createMemberFor($owner, [
            'name' => 'Aluno V1',
            'email' => 'aluno.v1@test.app',
            'phone' => '11988887777',
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'aluno.v1@test.app',
            'password' => 'password',
            'device_name' => 'student-app',
        ]);

        $response->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('session_type', 'student')
            ->assertJsonPath('user.id', (string) $member->id)
            ->assertJsonPath('user.email', 'aluno.v1@test.app')
            ->assertJsonPath('user.role', 'STUDENT')
            ->assertJsonPath('user.coachName', 'Coach Student Login')
            ->assertJsonStructure([
                'message',
                'token_type',
                'access_token',
                'session_type',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'image',
                    'phone',
                    'status',
                    'role',
                    'coachName',
                ],
                'client',
            ]);

        expect($response->json('access_token'))->not->toBeEmpty()
            ->and($owner->fresh()->tokens()->where('name', 'client-'.$member->id)->exists())->toBeTrue();
    });

    it('returns the authenticated user via /auth/me', function () {
        $owner = createOwner(['email' => 'me@test.app']);

        Sanctum::actingAs($owner);

        $this->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'parent_id',
                    'tenant_id',
                    'roles',
                    'created_at',
                ],
            ])
            ->assertJsonPath('data.id', $owner->id)
            ->assertJsonPath('data.email', 'me@test.app');
    });

    it('revokes the token on logout', function () {
        $owner = createOwner([
            'email' => 'logout@test.app',
            'password' => Hash::make('password'),
        ]);

        $login = $this->postJson('/api/v1/login', [
            'email' => 'logout@test.app',
            'password' => 'password',
            'device_name' => 'pest-logout',
        ])->assertOk();

        $token = $login->json('access_token');

        $this->withToken($token)
            ->postJson('/api/v1/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Logout realizado com sucesso.');

        expect($owner->fresh()->tokens()->count())->toBe(0);

        // Limpa o guard autenticado da request anterior (estado sticky do test case)
        $this->app['auth']->forgetGuards();

        $this->withToken($token)
            ->getJson('/api/v1/auth/me')
            ->assertUnauthorized()
            ->assertJsonPath('error', 'unauthenticated');
    });
});
