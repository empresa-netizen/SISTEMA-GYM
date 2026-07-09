<?php

namespace App\Jobs;

use App\Models\CoachFeedItem;
use App\Models\User;
use App\Notifications\InAppAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyFeedInteraction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $feedItemId,
        public int $actorUserId,
        public string $interaction = 'like', // like|comment
    ) {}

    public function handle(): void
    {
        $item = CoachFeedItem::query()->find($this->feedItemId);
        $actor = User::query()->find($this->actorUserId);

        if (! $item || ! $actor) {
            return;
        }

        $owner = User::query()->find($item->parent_id);

        if (! $owner || $owner->id === $actor->id) {
            return;
        }

        $isLike = $this->interaction === 'like';

        $owner->notify(new InAppAlert(
            title: $isLike ? 'Nova curtida no feed' : 'Novo comentario no feed',
            body: $isLike
                ? $actor->name.' curtiu “'.$item->title.'”.'
                : $actor->name.' comentou em “'.$item->title.'”.',
            url: url('/feed'),
            icon: $isLike ? 'ri-heart-fill' : 'ri-chat-3-line',
            level: 'success',
        ));
    }
}
