<?php

use App\Models\Event;
use Laravel\Sanctum\Sanctum;

describe('API V1 events contract', function () {
    it('creates and updates events only for members in the authenticated tenant', function () {
        $owner = createOwner(['email' => 'v1.events.owner@test.app']);
        $member = createMemberFor($owner, [
            'name' => 'Ana Evento',
            'email' => 'ana.evento@test.app',
        ]);
        $otherOwner = createOwner(['email' => 'v1.events.other-owner@test.app']);
        $otherMember = createMemberFor($otherOwner, ['email' => 'outro.evento@test.app']);

        Sanctum::actingAs($owner);

        $event = $this->postJson('/api/v1/events', [
            'member_id' => $member->id,
            'title' => 'Avaliação física',
            'description' => 'Check-up mensal',
            'start_time' => now()->addDay()->setTime(10, 0)->toISOString(),
            'end_time' => now()->addDay()->setTime(11, 0)->toISOString(),
            'location' => 'Academia',
        ])
            ->assertCreated()
            ->assertJsonPath('data.member_id', $member->id)
            ->assertJsonPath('data.member.name', 'Ana Evento');

        $this->patchJson('/api/v1/events/'.$event->json('data.id'), [
            'member_id' => null,
            'title' => 'Avaliação sem aluno fixo',
        ])
            ->assertOk()
            ->assertJsonPath('data.member_id', null)
            ->assertJsonPath('data.title', 'Avaliação sem aluno fixo');

        $this->postJson('/api/v1/events', [
            'member_id' => $otherMember->id,
            'title' => 'Evento vazado',
            'start_time' => now()->addDay()->setTime(12, 0)->toISOString(),
            'end_time' => now()->addDay()->setTime(13, 0)->toISOString(),
        ])->assertNotFound();

        $this->patchJson('/api/v1/events/'.$event->json('data.id'), [
            'member_id' => $otherMember->id,
        ])->assertNotFound();

        expect(Event::query()
            ->where('parent_id', $owner->id)
            ->where('title', 'Evento vazado')
            ->exists())->toBeFalse();
    });
});
