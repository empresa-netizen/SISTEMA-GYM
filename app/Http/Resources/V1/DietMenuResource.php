<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\DietMenu */
class DietMenuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'meals_count' => $this->meals_count,
            'total_calories' => $this->total_calories !== null ? (float) $this->total_calories : null,
            'description' => $this->description,
            'created_at' => optional($this->created_at)?->toIso8601String(),
        ];
    }
}
