<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Exercise */
class ExerciseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'vimeo_id' => $this->vimeo_id,
            'vimeo_url' => $this->vimeo_url,
            'embed_url' => $this->embed_url,
            'duration_seconds' => $this->duration_seconds,
            'source' => $this->source,
            'created_at' => optional($this->created_at)?->toIso8601String(),
        ];
    }
}
