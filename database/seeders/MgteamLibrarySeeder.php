<?php

namespace Database\Seeders;

use App\Models\LibraryCourse;
use App\Models\LibraryWorkout;
use App\Models\User;
use Illuminate\Database\Seeder;

class MgteamLibrarySeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::role('owner')->first();
        if (! $owner) {
            return;
        }

        LibraryWorkout::where('parent_id', $owner->id)->delete();
        LibraryCourse::where('parent_id', $owner->id)->delete();

        $workouts = [
            [
                'title' => 'Full Body 3x — Iniciante',
                'focus' => 'Adaptação',
                'duration_weeks' => 4,
                'sessions_per_week' => 3,
                'level' => 'beginner',
                'status' => 'published',
                'description' => 'Template base para novos alunos.',
            ],
            [
                'title' => 'Upper/Lower — Hipertrofia',
                'focus' => 'Hipertrofia',
                'duration_weeks' => 8,
                'sessions_per_week' => 4,
                'level' => 'intermediate',
                'status' => 'published',
                'description' => 'Divisão superior/inferior com progressão linear.',
            ],
            [
                'title' => 'Push Pull Legs — Avançado',
                'focus' => 'Performance',
                'duration_weeks' => 6,
                'sessions_per_week' => 6,
                'level' => 'advanced',
                'status' => 'draft',
                'description' => 'Volume alto para alunos avançados.',
            ],
        ];

        foreach ($workouts as $row) {
            LibraryWorkout::create(array_merge($row, ['parent_id' => $owner->id]));
        }

        $courses = [
            [
                'title' => 'Onboarding do aluno',
                'product' => 'Consultoria',
                'modules_count' => 3,
                'lessons_count' => 8,
                'status' => 'published',
                'description' => 'Trilha inicial de hábitos e expectativas.',
            ],
            [
                'title' => 'Nutrição prática',
                'product' => 'Mentoria',
                'modules_count' => 4,
                'lessons_count' => 12,
                'status' => 'published',
                'description' => 'Fundamentos de dieta e aderência.',
            ],
        ];

        foreach ($courses as $row) {
            LibraryCourse::create(array_merge($row, ['parent_id' => $owner->id]));
        }

        $this->command?->info('✅ Biblioteca: '.count($workouts).' templates de treino + '.count($courses).' cursos');
    }
}
