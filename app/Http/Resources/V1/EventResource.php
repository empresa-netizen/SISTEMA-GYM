<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Event */
class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'member_id' => $this->member_id,
            'title' => $this->title,
            'description' => $this->description,
            'start_time' => optional($this->start_time)?->toIso8601String(),
            'end_time' => optional($this->end_time)?->toIso8601String(),
            'location' => $this->location,
            'max_participants' => $this->max_participants,
            'registered_count' => $this->registered_count,
            'status' => $this->status,
            'image' => $this->image,
            'member' => new MemberResource($this->whenLoaded('member')),
            'created_at' => optional($this->created_at)?->toIso8601String(),
            'updated_at' => optional($this->updated_at)?->toIso8601String(),
        ];
    }
}
