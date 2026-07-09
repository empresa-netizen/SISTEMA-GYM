<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\MembershipPlan */
class MembershipPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => (float) $this->price,
            'duration_type' => $this->duration_type,
            'duration_value' => $this->duration_value,
            'is_active' => (bool) $this->is_active,
            'features' => $this->features,
            'max_classes' => $this->max_classes,
            'personal_training' => (bool) $this->personal_training,
            'created_at' => optional($this->created_at)?->toIso8601String(),
        ];
    }
}
