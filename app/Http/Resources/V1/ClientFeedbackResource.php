<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ClientFeedback */
class ClientFeedbackResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'member_id' => $this->member_id,
            'status' => $this->status,
            'message' => $this->message,
            'photo_path' => $this->photo_path,
            'rating' => $this->rating,
            'member' => new MemberResource($this->whenLoaded('member')),
            'created_at' => optional($this->created_at)?->toIso8601String(),
        ];
    }
}
