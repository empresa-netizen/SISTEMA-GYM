<?php

use App\Models\CoachFeedItem;
use App\Models\CommunityGroup;
use App\Models\CommunityPost;

describe('Mobile professional API', function () {
    it('returns tenant scoped posts and community data for the authenticated coach', function () {
        $owner = createOwner(['email' => 'mobile.professional.owner@test.app']);
        $member = createMemberFor($owner, [
            'name' => 'Aluno Profissional',
            'email' => 'aluno.professional@test.app',
        ]);
        $otherOwner = createOwner(['email' => 'mobile.professional.other@test.app']);

        CoachFeedItem::query()->create([
            'parent_id' => $owner->id,
            'author_id' => $owner->id,
            'member_id' => $member->id,
            'type' => 'POST',
            'title' => 'Aviso do ciclo',
            'description' => 'Semana de progressão de carga.',
            'likes_count' => 2,
            'comments_count' => 1,
        ]);

        CoachFeedItem::query()->create([
            'parent_id' => $otherOwner->id,
            'author_id' => $otherOwner->id,
            'type' => 'POST',
            'title' => 'Outro tenant',
        ]);

        $group = CommunityGroup::query()->create([
            'parent_id' => $owner->id,
            'name' => 'Equipe Performance',
            'description' => 'Grupo de acompanhamento do ciclo.',
            'members_count' => 8,
        ]);

        CommunityPost::query()->create([
            'parent_id' => $owner->id,
            'community_group_id' => $group->id,
            'member_id' => $member->id,
            'content' => 'Check-in do aluno no grupo.',
            'likes_count' => 4,
        ]);

        CommunityGroup::query()->create([
            'parent_id' => $otherOwner->id,
            'name' => 'Grupo vazado',
        ]);

        $login = $this->postJson('/api/auth/professional/login', [
            'email' => 'mobile.professional.owner@test.app',
            'password' => 'password',
        ])->assertOk();

        $this->withToken($login->json('token'))
            ->getJson('/api/professional/posts')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.title', 'Aviso do ciclo')
            ->assertJsonPath('0.member.name', 'Aluno Profissional');

        $this->withToken($login->json('token'))
            ->getJson('/api/professional/community')
            ->assertOk()
            ->assertJsonPath('groups.0.id', $group->id)
            ->assertJsonPath('groups.0.name', 'Equipe Performance')
            ->assertJsonPath('groups.0.posts_count', 1)
            ->assertJsonPath('groups.0.posts.0.author_name', 'Aluno Profissional')
            ->assertJsonPath('recent_posts.0.content', 'Check-in do aluno no grupo.');
    });
});
