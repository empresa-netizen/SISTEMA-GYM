<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Workout */
class WorkoutResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $totalActivities = $this->resource->getAttribute('activities_total')
            ?? ($this->relationLoaded('activities') ? $this->activities->count() : $this->activities()->count());
        $completedActivities = $this->resource->getAttribute('activities_completed')
            ?? ($this->relationLoaded('activities') ? $this->activities->where('is_completed', true)->count() : $this->activities()->where('is_completed', true)->count());
        $completionPercentage = $totalActivities > 0
            ? (int) round(($completedActivities / $totalActivities) * 100)
            : 0;

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
            'activities_total' => (int) $totalActivities,
            'activities_completed' => (int) $completedActivities,
            'completion_percentage' => $completionPercentage,
            'member' => new MemberResource($this->whenLoaded('member')),
            'activities' => $this->whenLoaded('activities', function () {
                return $this->activities->map(function ($activity) {
                    return [
                        'id' => $activity->id,
                        'workout_id' => $activity->workout_id,
                        'exercise_name' => $activity->exercise_name,
                        'description' => $activity->description,
                        'sets' => $activity->sets,
                        'reps' => $activity->reps,
                        'duration_minutes' => $activity->duration_minutes,
                        'rest_seconds' => $activity->rest_seconds,
                        'weight_kg' => $activity->weight_kg !== null ? (float) $activity->weight_kg : null,
                        'order' => $activity->order,
                        'is_completed' => (bool) $activity->is_completed,
                        'notes' => $activity->notes,
                        'details' => $activity->details,
                        'logs' => $activity->relationLoaded('logs')
                            ? $activity->logs->map(function ($log) {
                                return [
                                    'id' => $log->id,
                                    'workout_activity_id' => $log->workout_activity_id,
                                    'sets' => $log->sets,
                                    'reps' => $log->reps,
                                    'weight_kg' => $log->weight_kg !== null ? (float) $log->weight_kg : null,
                                    'is_completed' => (bool) $log->is_completed,
                                    'notes' => $log->notes,
                                    'logged_at' => optional($log->logged_at)?->toIso8601String(),
                                    'created_at' => optional($log->created_at)?->toIso8601String(),
                                ];
                            })->values()
                            : [],
                        'created_at' => optional($activity->created_at)?->toIso8601String(),
                        'updated_at' => optional($activity->updated_at)?->toIso8601String(),
                    ];
                })->values();
            }),
            'created_at' => optional($this->created_at)?->toIso8601String(),
            'updated_at' => optional($this->updated_at)?->toIso8601String(),
        ];
    }
}
