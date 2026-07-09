<?php

use App\Jobs\NotifyFeedInteraction;
use App\Models\CoachFeedItem;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;

describe('API V1 / Feed queue contracts', function () {
    it('dispatches NotifyFeedInteraction when a feed post is liked', function () {
        Queue::fake();

        $owner = createOwner(['email' => 'feed.owner@test.app']);

        // Mesmo tenant (parent_id) para passar no TenantScope + route model binding
        $actor = User::factory()->create([
            'email' => 'feed.actor@test.app',
            'parent_id' => $owner->id,
            'password' => Hash::make('password'),
        ]);
        $actor->assignRole('manager');

        $item = CoachFeedItem::query()->create([
            'parent_id' => $owner->id,
            'author_id' => $owner->id,
            'member_id' => null,
            'type' => 'POST',
            'title' => 'Treino da semana',
            'description' => 'Foco em força',
            'meta' => 'Publicado pelo coach',
            'likes_count' => 0,
            'comments_count' => 0,
        ]);

        $this->actingAs($actor)
            ->from('/feed')
            ->post(route('feed.like', $item))
            ->assertRedirect();

        Queue::assertPushed(NotifyFeedInteraction::class, function (NotifyFeedInteraction $job) use ($item, $actor) {
            return $job->feedItemId === $item->id
                && $job->actorUserId === $actor->id
                && $job->interaction === 'like';
        });
    });

    it('dispatches NotifyFeedInteraction when a feed post is commented', function () {
        Queue::fake();

        $owner = createOwner(['email' => 'feed.owner2@test.app']);

        $actor = User::factory()->create([
            'email' => 'feed.actor2@test.app',
            'parent_id' => $owner->id,
            'password' => Hash::make('password'),
        ]);
        $actor->assignRole('manager');

        $item = CoachFeedItem::query()->create([
            'parent_id' => $owner->id,
            'author_id' => $owner->id,
            'type' => 'POST',
            'title' => 'Dieta atualizada',
            'description' => null,
            'meta' => null,
            'likes_count' => 0,
            'comments_count' => 0,
        ]);

        $this->actingAs($actor)
            ->from('/feed')
            ->post(route('feed.comment', $item), [
                'body' => 'Excelente post!',
            ])
            ->assertRedirect();

        Queue::assertPushed(NotifyFeedInteraction::class, function (NotifyFeedInteraction $job) use ($item, $actor) {
            return $job->feedItemId === $item->id
                && $job->actorUserId === $actor->id
                && $job->interaction === 'comment';
        });
    });
});
