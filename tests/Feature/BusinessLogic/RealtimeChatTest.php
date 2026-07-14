<?php

use App\Broadcasting\ChatConversationChannel;
use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\User;
use App\Services\ChatMessenger;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

describe('Realtime chat broadcasting', function () {
    it('dispatches MessageSent when coach sends a message', function () {
        Event::fake([MessageSent::class]);
        Notification::fake();

        $owner = createOwner(['email' => 'rt.coach@test.app']);
        $memberUser = User::factory()->create([
            'email' => 'rt.member.user@test.app',
            'password' => Hash::make('password'),
            'parent_id' => $owner->id,
        ]);
        $member = createMemberFor($owner, [
            'name' => 'Aluno Realtime',
            'email' => 'rt.member@test.app',
            'user_id' => $memberUser->id,
        ]);

        $conversation = Conversation::query()->create([
            'parent_id' => $owner->id,
            'member_id' => $member->id,
            'last_message_at' => now(),
        ]);

        app(ChatMessenger::class)->sendFromCoach($conversation, 'Ping realtime');

        Event::assertDispatched(MessageSent::class, function (MessageSent $event) use ($conversation) {
            return $event->message->conversation_id === $conversation->id
                && $event->message->content === 'Ping realtime'
                && $event->broadcastAs() === 'MessageSent'
                && collect($event->broadcastOn())->contains(fn ($channel) => $channel->name === 'private-chat.'.$conversation->id);
        });
    });

    it('authorizes chat channel for coach and member only', function () {
        $owner = createOwner(['email' => 'rt.auth.coach@test.app']);
        $stranger = createOwner(['email' => 'rt.auth.stranger@test.app']);
        $memberUser = User::factory()->create([
            'email' => 'rt.auth.member@test.app',
            'password' => Hash::make('password'),
            'parent_id' => $owner->id,
        ]);
        $member = createMemberFor($owner, [
            'email' => 'rt.auth.member.profile@test.app',
            'user_id' => $memberUser->id,
        ]);

        $conversation = Conversation::query()->create([
            'parent_id' => $owner->id,
            'member_id' => $member->id,
        ]);

        $channel = app(ChatConversationChannel::class);

        expect($channel->join($owner, $conversation->id))->toBe(['role' => 'coach'])
            ->and($channel->join($memberUser, $conversation->id))->toBe(['role' => 'member'])
            ->and($channel->join($stranger, $conversation->id))->toBeFalse();
    });

    it('limits client tokens to the member conversation they represent', function () {
        $owner = createOwner(['email' => 'rt.client-token.coach@test.app']);
        $memberA = createMemberFor($owner, ['email' => 'rt.client-token.a@test.app']);
        $memberB = createMemberFor($owner, ['email' => 'rt.client-token.b@test.app']);

        $conversationA = Conversation::query()->create([
            'parent_id' => $owner->id,
            'member_id' => $memberA->id,
        ]);
        $conversationB = Conversation::query()->create([
            'parent_id' => $owner->id,
            'member_id' => $memberB->id,
        ]);

        $token = $owner->createToken('client-'.$memberA->id, ['client:'.$memberA->id]);
        $ownerWithClientToken = $owner->fresh()->withAccessToken($token->accessToken);
        $channel = app(ChatConversationChannel::class);

        expect($channel->join($ownerWithClientToken, $conversationA->id))->toBe(['role' => 'member'])
            ->and($channel->join($ownerWithClientToken, $conversationB->id))->toBeFalse();
    });
});
