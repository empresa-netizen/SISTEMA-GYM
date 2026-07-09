<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Conversation */
class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'member_id' => $this->member_id,
            'last_message' => $this->last_message,
            'last_message_at' => optional($this->last_message_at)?->toIso8601String(),
            'unread_by_coach' => (bool) $this->unread_by_coach,
            'member' => new MemberResource($this->whenLoaded('member')),
            'messages' => MessageResource::collection($this->whenLoaded('messages')),
            'created_at' => optional($this->created_at)?->toIso8601String(),
            'updated_at' => optional($this->updated_at)?->toIso8601String(),
        ];
    }
}
