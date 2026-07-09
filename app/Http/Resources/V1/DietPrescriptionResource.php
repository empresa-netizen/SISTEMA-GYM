<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\DietPrescription */
class DietPrescriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'member_id' => $this->member_id,
            'diet_menu_id' => $this->diet_menu_id,
            'title' => $this->title,
            'notes' => $this->notes,
            'status' => $this->status,
            'delivery_status' => $this->delivery_status,
            'scheduled_at' => optional($this->scheduled_at)?->toIso8601String(),
            'sent_at' => optional($this->sent_at)?->toIso8601String(),
            'member' => new MemberResource($this->whenLoaded('member')),
            'diet_menu' => new DietMenuResource($this->whenLoaded('dietMenu')),
            'created_at' => optional($this->created_at)?->toIso8601String(),
            'updated_at' => optional($this->updated_at)?->toIso8601String(),
        ];
    }
}
