<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Member */
class MemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'member_code' => $this->member_id,
            'parent_id' => $this->parent_id,
            'user_id' => $this->user_id,
            'coach_user_id' => $this->coach_user_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'date_of_birth' => optional($this->date_of_birth)?->toDateString(),
            'gender' => $this->gender,
            'address' => $this->address,
            'photo' => $this->photo,
            'status' => $this->status,
            'membership_plan_id' => $this->membership_plan_id,
            'membership_start_date' => optional($this->membership_start_date)?->toDateString(),
            'membership_end_date' => optional($this->membership_end_date)?->toDateString(),
            'membership_plan' => new MembershipPlanResource($this->whenLoaded('membershipPlan')),
            'anamnesis' => $this->whenLoaded('anamnesis'),
            'health_records' => $this->whenLoaded('healthRecords'),
            'photos' => $this->whenLoaded('photos'),
            'feedbacks' => ClientFeedbackResource::collection($this->whenLoaded('feedbacks')),
            'diet_prescriptions' => DietPrescriptionResource::collection($this->whenLoaded('dietPrescriptions')),
            'member_notes' => $this->whenLoaded('memberNotes'),
            'created_at' => optional($this->created_at)?->toIso8601String(),
            'updated_at' => optional($this->updated_at)?->toIso8601String(),
        ];
    }
}
