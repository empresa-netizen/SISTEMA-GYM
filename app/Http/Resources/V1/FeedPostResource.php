<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\CoachFeedItem */
class FeedPostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'author_id' => $this->author_id,
            'member_id' => $this->member_id,
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'meta' => $this->meta,
            'image_path' => $this->image_path,
            'image_url' => $this->image_url,
            'likes_count' => (int) ($this->likes_count ?? 0),
            'comments_count' => (int) ($this->comments_count ?? 0),
            'member' => new MemberResource($this->whenLoaded('member')),
            'author' => new UserResource($this->whenLoaded('author')),
            'created_at' => optional($this->created_at)?->toIso8601String(),
            'updated_at' => optional($this->updated_at)?->toIso8601String(),
        ];
    }
}
