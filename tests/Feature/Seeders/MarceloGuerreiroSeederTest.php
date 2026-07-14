<?php

use App\Models\DietFood;
use App\Models\DietMenu;
use App\Models\DietPrescription;
use App\Models\Exercise;
use App\Models\Member;
use App\Models\Workout;
use App\Support\AdminCredentials;
use Database\Seeders\MarceloGuerreiroSeeder;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->importPath = storage_path('app/imports');
    $this->importFiles = [
        'mgteam_exercises.json',
        'mgteam_foods.json',
        'mgteam_member.json',
        'mgteam_workouts.json',
        'mgteam_diets.json',
    ];
    $this->mgImportBackup = [];

    File::ensureDirectoryExists($this->importPath);

    foreach ($this->importFiles as $fileName) {
        $path = $this->importPath.DIRECTORY_SEPARATOR.$fileName;
        $this->mgImportBackup[$fileName] = File::exists($path) ? File::get($path) : null;
        File::delete($path);
    }
});

afterEach(function () {
    foreach ($this->mgImportBackup as $fileName => $contents) {
        $path = $this->importPath.DIRECTORY_SEPARATOR.$fileName;

        if ($contents === null) {
            File::delete($path);

            continue;
        }

        File::put($path, $contents);
    }
});

it('imports Marcelo Guerreiro payloads from MGTEAM JSON files', function () {
    $owner = createOwner(['email' => AdminCredentials::CANONICAL_EMAIL]);

    File::put($this->importPath.'/mgteam_member.json', json_encode([
        'member' => [
            'name' => 'Marcelo Guerreiro',
            'email' => 'marcelo.guerreiro@example.test',
            'phone' => '11999990000',
            'gender' => 'male',
        ],
    ], JSON_THROW_ON_ERROR));

    File::put($this->importPath.'/mgteam_exercises.json', json_encode([
        ['name' => 'Supino reto', 'vimeo_id' => 123, 'duration_seconds' => 45],
    ], JSON_THROW_ON_ERROR));

    File::put($this->importPath.'/mgteam_foods.json', json_encode([
        ['name' => 'Arroz branco', 'food_group' => 'Carboidratos', 'calories' => 130, 'protein' => 2.7, 'carbs' => 28, 'fat' => 0.3],
    ], JSON_THROW_ON_ERROR));

    File::put($this->importPath.'/mgteam_workouts.json', json_encode([
        [
            'name' => 'Treino A',
            'activities' => [
                ['exercise_name' => 'Supino reto', 'sets' => 4, 'reps' => 10, 'rest_seconds' => 90],
            ],
        ],
    ], JSON_THROW_ON_ERROR));

    File::put($this->importPath.'/mgteam_diets.json', json_encode([
        [
            'name' => 'Dieta base',
            'meals' => [
                [
                    'name' => 'Almoço',
                    'foods' => [
                        ['food' => ['name' => 'Arroz branco', 'calories' => 130, 'protein' => 2.7, 'carbs' => 28, 'fat' => 0.3], 'quantity_in_grams' => 200],
                    ],
                ],
            ],
        ],
    ], JSON_THROW_ON_ERROR));

    $this->seed(MarceloGuerreiroSeeder::class);

    $member = Member::withoutGlobalScopes()
        ->where('parent_id', $owner->id)
        ->where('email', 'marcelo.guerreiro@example.test')
        ->firstOrFail();

    expect($member->name)->toBe('Marcelo Guerreiro')
        ->and(Exercise::withoutGlobalScopes()->where('parent_id', $owner->id)->where('name', 'Supino reto')->exists())->toBeTrue()
        ->and(DietFood::withoutGlobalScopes()->where('parent_id', $owner->id)->where('name', 'Arroz branco')->exists())->toBeTrue()
        ->and(Workout::withoutGlobalScopes()->where('parent_id', $owner->id)->where('member_id', $member->id)->where('name', 'Treino A')->first()?->activities()->count())->toBe(1)
        ->and(DietMenu::withoutGlobalScopes()->where('parent_id', $owner->id)->where('name', 'Dieta base')->first()?->computedMacros()['calories'])->toBe(260.0)
        ->and(DietPrescription::withoutGlobalScopes()->where('parent_id', $owner->id)->where('member_id', $member->id)->where('title', 'Dieta base')->exists())->toBeTrue();
});
