<?php

namespace App\Services;

use App\Models\MemberLogbook;
use App\Models\Workout;
use App\Models\WorkoutActivity;
use App\Models\WorkoutActivityLog;
use Illuminate\Support\Facades\DB;

class WorkoutSessionLogger
{
    public function logActivity(
        Workout $workout,
        WorkoutActivity $activity,
        array $payload
    ): WorkoutActivityLog {
        abort_unless((int) $activity->workout_id === (int) $workout->id, 404);
        abort_unless((int) $workout->member_id > 0, 422, 'Treino sem aluno vinculado.');

        return DB::transaction(function () use ($workout, $activity, $payload) {
            $completed = (bool) ($payload['is_completed'] ?? true);

            $activity->forceFill([
                'is_completed' => $completed,
                'weight_kg' => $payload['weight_kg'] ?? $activity->weight_kg,
                'sets' => $payload['sets'] ?? $activity->sets,
                'reps' => $payload['reps'] ?? $activity->reps,
            ])->save();

            $log = WorkoutActivityLog::query()->create([
                'parent_id' => $workout->parent_id,
                'member_id' => $workout->member_id,
                'workout_id' => $workout->id,
                'workout_activity_id' => $activity->id,
                'sets' => $payload['sets'] ?? $activity->sets,
                'reps' => $payload['reps'] ?? $activity->reps,
                'weight_kg' => $payload['weight_kg'] ?? $activity->weight_kg,
                'is_completed' => $completed,
                'notes' => $payload['notes'] ?? null,
                'logged_at' => now(),
            ]);

            $this->syncWorkoutStatus($workout);

            return $log;
        });
    }

    public function completeWorkout(
        Workout $workout,
        ?string $comment = null,
        ?int $rating = null,
        ?int $durationSeconds = null
    ): Workout {
        return DB::transaction(function () use ($workout, $comment, $rating, $durationSeconds) {
            $workout->activities()->update(['is_completed' => true]);
            $workout->forceFill(['status' => 'completed'])->save();

            $existingLogbook = MemberLogbook::query()
                ->where('parent_id', $workout->parent_id)
                ->where('member_id', $workout->member_id)
                ->where('type', 'TRAINING')
                ->whereDate('logged_at', today())
                ->where('metadata->source', 'student_workout_complete')
                ->where('metadata->workout_id', $workout->id)
                ->first();

            if ($existingLogbook) {
                return $workout->fresh('activities.logs');
            }

            MemberLogbook::query()->create([
                'parent_id' => $workout->parent_id,
                'member_id' => $workout->member_id,
                'type' => 'TRAINING',
                'title' => 'Treino concluído: '.$workout->name,
                'rating' => $rating ?? 5,
                'metadata' => [
                    'source' => 'student_workout_complete',
                    'workout_id' => $workout->id,
                    'workout_name' => $workout->name,
                    'activities_total' => $workout->activities()->count(),
                    'duration_seconds' => $durationSeconds,
                ],
                'comment' => $comment ?: 'Sessão finalizada pelo app do aluno.',
                'logged_at' => now(),
            ]);

            return $workout->fresh('activities.logs');
        });
    }

    public function uncompleteActivity(Workout $workout, WorkoutActivity $activity): Workout
    {
        abort_unless((int) $activity->workout_id === (int) $workout->id, 404);
        abort_unless((int) $workout->member_id > 0, 422, 'Treino sem aluno vinculado.');

        return DB::transaction(function () use ($workout, $activity) {
            $activity->forceFill(['is_completed' => false])->save();

            $latestCompletionLog = WorkoutActivityLog::query()
                ->where('parent_id', $workout->parent_id)
                ->where('member_id', $workout->member_id)
                ->where('workout_id', $workout->id)
                ->where('workout_activity_id', $activity->id)
                ->where('is_completed', true)
                ->latest('logged_at')
                ->latest('id')
                ->first();

            if ($latestCompletionLog) {
                $latestCompletionLog->forceFill(['is_completed' => false])->save();
            }

            $this->syncWorkoutStatus($workout);

            return $workout->fresh('activities.logs');
        });
    }

    private function syncWorkoutStatus(Workout $workout): void
    {
        $total = $workout->activities()->count();
        if ($total === 0) {
            return;
        }

        $done = $workout->activities()->where('is_completed', true)->count();
        if ($done >= $total) {
            $workout->forceFill(['status' => 'completed'])->save();
        } elseif ($workout->status === 'completed') {
            $workout->forceFill(['status' => 'active'])->save();
            $this->forgetWorkoutCompletionLogbook($workout);
        }
    }

    private function forgetWorkoutCompletionLogbook(Workout $workout): void
    {
        MemberLogbook::query()
            ->where('parent_id', $workout->parent_id)
            ->where('member_id', $workout->member_id)
            ->where('type', 'TRAINING')
            ->whereDate('logged_at', today())
            ->where('metadata->source', 'student_workout_complete')
            ->where('metadata->workout_id', $workout->id)
            ->delete();
    }
}
