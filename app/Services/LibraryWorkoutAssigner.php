<?php

namespace App\Services;

use App\Models\LibraryWorkout;
use App\Models\Member;
use App\Models\Workout;
use Illuminate\Support\Facades\DB;

class LibraryWorkoutAssigner
{
    /**
     * Deep clone: cria Workout do aluno + cópias literais dos exercícios do template.
     * Edições posteriores no template NÃO afetam a ficha prescrita.
     */
    public function assign(LibraryWorkout $template, Member $member): Workout
    {
        abort_unless($template->parent_id === $member->parent_id, 403);

        $template->loadMissing('activities');

        return DB::transaction(function () use ($template, $member) {
            $workout = Workout::withoutGlobalScopes()->create([
                'parent_id' => $member->parent_id,
                'member_id' => $member->id,
                'name' => $template->title,
                'description' => $template->description,
                'notes' => trim(($template->notes ? $template->notes."\n" : '').'Importado da biblioteca #'.$template->id),
                'workout_date' => now()->toDateString(),
                'status' => 'active',
            ]);

            foreach ($template->activities as $index => $activity) {
                $workout->activities()->create([
                    'exercise_name' => $activity->exercise_name,
                    'description' => $activity->description,
                    'sets' => $activity->sets,
                    'reps' => $activity->reps,
                    'duration_minutes' => $activity->duration_minutes,
                    'rest_seconds' => $activity->rest_seconds,
                    'weight_kg' => $activity->weight_kg,
                    'order' => $activity->order ?? $index,
                    'notes' => $activity->notes,
                    'is_completed' => false,
                ]);
            }

            return $workout->load('activities');
        });
    }
}
