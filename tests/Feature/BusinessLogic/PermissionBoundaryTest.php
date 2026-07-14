<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

describe('Permission boundary business logic', function () {
    it('returns 403 when a trainer from another team accesses a member', function () {
        $ownerA = createOwner(['email' => 'perm.a@test.app']);
        $ownerB = createOwner(['email' => 'perm.b@test.app']);

        $memberA = createMemberFor($ownerA, [
            'name' => 'Cliente Time A',
            'email' => 'cliente.a@test.app',
        ]);

        $assistant = User::factory()->create([
            'name' => 'Treinador Assistente B',
            'email' => 'assistente.b@test.app',
            'password' => Hash::make('password'),
            'parent_id' => $ownerB->id,
        ]);
        $assistant->assignRole('trainer');

        Sanctum::actingAs($assistant);

        $this->getJson('/api/v1/members/'.$memberA->id)
            ->assertForbidden()
            ->assertJsonPath('error', 'http_error');
    });

    it('allows the owning team trainer to access the member', function () {
        $owner = createOwner(['email' => 'perm.owner@test.app']);
        $member = createMemberFor($owner, [
            'name' => 'Cliente Time Owner',
            'email' => 'cliente.owner@test.app',
        ]);

        $trainer = User::factory()->create([
            'name' => 'Treinador da Casa',
            'email' => 'trainer.casa@test.app',
            'password' => Hash::make('password'),
            'parent_id' => $owner->id,
        ]);
        $trainer->assignRole('trainer');

        Sanctum::actingAs($trainer);

        $this->getJson('/api/v1/members/'.$member->id)
            ->assertOk()
            ->assertJsonPath('data.id', $member->id)
            ->assertJsonPath('data.parent_id', $owner->id);
    });
});
