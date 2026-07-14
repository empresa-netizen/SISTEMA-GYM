<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class MgteamExerciseSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::role('owner')->first();
        $path = database_path('data/mgteam_vimeo_exercises.json');

        if (! $owner || ! File::exists($path)) {
            $this->command->warn('Exercícios não importados: owner ou JSON ausente.');

            return;
        }

        $items = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);

        foreach ($items as $item) {
            Exercise::withoutGlobalScopes()->updateOrCreate(
                [
                    'parent_id' => $owner->id,
                    'vimeo_id' => $item['vimeo_id'] ?? null,
                ],
                [
                    'name' => $item['name'],
                    'vimeo_url' => $item['vimeo_url'] ?? null,
                    'embed_url' => $item['embed_url'] ?? null,
                    'duration_seconds' => $item['duration_seconds'] ?? null,
                    'source' => $item['source'] ?? null,
                ]
            );
        }

        $this->command->info('✅ '.count($items).' exercícios importados do catálogo local');
    }
}
