<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutActivity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class MgteamWorkoutSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::role('owner')->first();
        $path = database_path('data/mgteam_vimeo_exercises.json');

        if (! File::exists($path)) {
            $this->command->warn('Arquivo de exercícios não encontrado: '.$path);

            return;
        }

        $exercises = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
        $members = Member::where('parent_id', $owner->id)->get();

        if ($members->isEmpty()) {
            $this->command->warn('Nenhum cliente encontrado para prescrever treinos.');

            return;
        }

        $workoutTemplates = [
            [
                'member_index' => 0,
                'name' => 'Treino A — Membros Inferiores',
                'description' => 'Baseado nos vídeos MGTEAM Platform (Vimeo)',
                'exercise_indexes' => [0, 1, 2, 8, 9],
            ],
            [
                'member_index' => 1,
                'name' => 'Treino B — Superior + Core',
                'description' => 'Prescrição inicial com material local MGTEAM Platform',
                'exercise_indexes' => [4, 5, 17, 18, 19],
            ],
        ];

        foreach ($workoutTemplates as $template) {
            $member = $members[$template['member_index']] ?? $members->first();

            $workout = Workout::withoutGlobalScopes()->create([
                'parent_id' => $owner->id,
                'member_id' => $member->id,
                'trainer_id' => null,
                'name' => $template['name'],
                'description' => $template['description'],
                'workout_date' => now()->toDateString(),
                'status' => 'active',
            ]);

            foreach ($template['exercise_indexes'] as $order => $exerciseIndex) {
                $exercise = $exercises[$exerciseIndex] ?? null;

                if (! $exercise) {
                    continue;
                }

                WorkoutActivity::create([
                    'workout_id' => $workout->id,
                    'exercise_name' => ucfirst($exercise['name']),
                    'description' => $exercise['embed_url'],
                    'sets' => 3,
                    'reps' => 12,
                    'duration_minutes' => max(1, (int) ceil(($exercise['duration_seconds'] ?? 30) / 60)),
                    'rest_seconds' => 60,
                    'order' => $order + 1,
                    'is_completed' => false,
                    'notes' => 'Vídeo: '.$exercise['vimeo_url'],
                ]);
            }
        }

        $this->command->info('✅ 2 treinos criados com exercícios do catálogo Vimeo local');
    }
}
