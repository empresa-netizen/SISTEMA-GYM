<?php

use App\Models\CoachFeedItem;
use Laravel\Sanctum\Sanctum;

describe('API V1 feed contract', function () {
    it('publishes feed items only for members in the authenticated tenant', function () {
        $owner = createOwner(['email' => 'v1.feed.owner@test.app']);
        $member = createMemberFor($owner, [
            'name' => 'Ana Feed',
            'email' => 'ana.feed@test.app',
        ]);
        $otherOwner = createOwner(['email' => 'v1.feed.other-owner@test.app']);
        $otherMember = createMemberFor($otherOwner, ['email' => 'outro.feed@test.app']);

        Sanctum::actingAs($owner);

        $this->postJson('/api/v1/feed', [
            'member_id' => $member->id,
            'type' => 'POST',
            'title' => 'Treino liberado',
            'description' => 'Seu treino da semana está no app.',
            'meta' => 'Aviso individual',
        ])
            ->assertCreated()
            ->assertJsonPath('data.member_id', $member->id)
            ->assertJsonPath('data.member.name', 'Ana Feed')
            ->assertJsonPath('data.title', 'Treino liberado');

        $this->postJson('/api/v1/feed', [
            'member_id' => $otherMember->id,
            'type' => 'POST',
            'title' => 'Post vazado',
        ])->assertNotFound();

        expect(CoachFeedItem::query()
            ->where('parent_id', $owner->id)
            ->where('title', 'Post vazado')
            ->exists())->toBeFalse();
    });
});
