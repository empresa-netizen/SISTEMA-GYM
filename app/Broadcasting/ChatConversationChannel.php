<?php

namespace App\Broadcasting;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Str;

class ChatConversationChannel
{
    /**
     * @return array{role: string}|false
     */
    public function join(User $user, int|string $conversationId): array|false
    {
        $conversation = Conversation::withoutGlobalScopes()
            ->with('member')
            ->find($conversationId);

        if (! $conversation) {
            return false;
        }

        $clientMemberId = $this->clientMemberIdFromToken($user);

        if ($clientMemberId !== null) {
            return (int) $conversation->member_id === $clientMemberId
                ? ['role' => 'member']
                : false;
        }

        if ((int) $user->id === (int) $conversation->parent_id) {
            return ['role' => 'coach'];
        }

        $member = $conversation->member;
        if ($member && (int) $member->user_id === (int) $user->id) {
            return ['role' => 'member'];
        }

        return false;
    }

    private function clientMemberIdFromToken(User $user): ?int
    {
        $token = $user->currentAccessToken();
        if (! $token) {
            return null;
        }

        if (Str::startsWith($token->name, 'client-')) {
            return (int) Str::after($token->name, 'client-');
        }

        foreach ($token->abilities ?? [] as $ability) {
            if (Str::startsWith($ability, 'client:')) {
                return (int) Str::after($ability, 'client:');
            }
        }

        return null;
    }
}
