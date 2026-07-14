<?php

namespace App\Services;

use App\Events\MessageSent;
use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\Member;
use App\Models\User;
use App\Notifications\InAppAlert;
use Illuminate\Support\Facades\Notification;

class ChatMessenger
{
    public function sendFromCoach(Conversation $conversation, string $content): ChatMessage
    {
        $message = $conversation->messages()->create([
            'sender_type' => 'coach',
            'content' => $content,
        ]);

        $conversation->update([
            'last_message' => $content,
            'last_message_at' => now(),
            'unread_by_coach' => false,
        ]);

        broadcast(new MessageSent($message))->toOthers();

        $member = $conversation->member()->with('user')->first();
        if ($member?->user) {
            Notification::send($member->user, new InAppAlert(
                title: 'Nova mensagem do coach',
                body: \Illuminate\Support\Str::limit($content, 120),
                url: url('/messages?conversation='.$conversation->id),
                icon: 'ri-message-3-line',
                level: 'info',
            ));
        }

        return $message;
    }

    public function sendFromMember(Member $member, string $content): array
    {
        $conversation = Conversation::query()->firstOrCreate(
            [
                'parent_id' => $member->parent_id,
                'member_id' => $member->id,
            ],
            [
                'last_message_at' => now(),
            ]
        );

        $message = $conversation->messages()->create([
            'sender_type' => 'member',
            'content' => $content,
        ]);

        $conversation->update([
            'last_message' => $content,
            'last_message_at' => now(),
            'unread_by_coach' => true,
        ]);

        broadcast(new MessageSent($message))->toOthers();

        $owner = User::query()->find($member->parent_id);
        if ($owner) {
            Notification::send($owner, new InAppAlert(
                title: 'Nova mensagem de '.$member->name,
                body: \Illuminate\Support\Str::limit($content, 120),
                url: url('/messages?conversation='.$conversation->id),
                icon: 'ri-message-3-line',
                level: 'info',
            ));
        }

        return compact('conversation', 'message');
    }

    public function markCoachMessagesRead(Conversation $conversation): int
    {
        return $conversation->messages()
            ->where('sender_type', 'coach')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function markMemberMessagesRead(Conversation $conversation): int
    {
        $updated = $conversation->messages()
            ->where('sender_type', 'member')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $conversation->update(['unread_by_coach' => false]);

        return $updated;
    }
}
