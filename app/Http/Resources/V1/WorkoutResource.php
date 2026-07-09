<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Workout */
class WorkoutResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'workout_code' => $this->workout_id,
            'member_id' => $this->member_id,
            'trainer_id' => $this->trainer_id,
            'name' => $this->name,
            'description' => $this->description,
            'workout_date' => optional($this->workout_date)?->toDateString(),
            'status' => $this->status,
            'notes' => $this->notes,
            'member' => new MemberResource($this->whenLoaded('member')),
            'activities' => $this->whenLoaded('activities'),
            'created_at' => optional($this->created_at)?->toIso8601String(),
            'updated_at' => optional($this->updated_at)?->toIso8601String(),
        ];
    }
}
