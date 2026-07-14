<?php

namespace Database\Seeders;

use App\Models\DietFood;
use App\Models\DietMeal;
use App\Models\DietMenu;
use App\Models\DietPrescription;
use App\Models\Exercise;
use App\Models\Member;
use App\Models\User;
use App\Models\Workout;
use App\Support\AdminCredentials;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;

class MarceloGuerreiroSeeder extends Seeder
{
    private const IMPORT_FILES = [
        'exercises' => 'mgteam_exercises.json',
        'foods' => 'mgteam_foods.json',
        'member' => 'mgteam_member.json',
        'workouts' => 'mgteam_workouts.json',
        'diets' => 'mgteam_diets.json',
    ];

    public function run(): void
    {
        $owner = $this->resolveOwner();
        $payloads = $this->loadPayloads();

        DB::transaction(function () use ($owner, $payloads): void {
            $member = $this->upsertMember($owner, $this->memberPayload($payloads));

            $exerciseCount = $this->upsertExercises($owner, $this->exercisePayloads($payloads));
            $foodCount = $this->upsertFoods($owner, $this->foodPayloads($payloads));
            $workoutCount = $this->upsertWorkouts($owner, $member, $this->workoutPayloads($payloads));
            $dietCount = $this->upsertDiets($owner, $member, $this->dietPayloads($payloads));

            $this->command?->info("✅ Marcelo Guerreiro importado: {$exerciseCount} exercícios, {$foodCount} alimentos, {$workoutCount} treinos, {$dietCount} dietas.");
        });
    }

    private function resolveOwner(): User
    {
        $owner = User::query()
            ->whereIn('email', [
                AdminCredentials::CANONICAL_EMAIL,
                AdminCredentials::LEGACY_ADMIN_EMAIL,
                AdminCredentials::LEGACY_COACH_EMAIL,
            ])
            ->first()
            ?? User::role('owner')->first();

        if (! $owner) {
            throw new RuntimeException('Nenhum usuário owner encontrado. Rode MgteamUserSeeder/UserSeeder antes do MarceloGuerreiroSeeder.');
        }

        return $owner;
    }

    /**
     * @return array{exercises: ?array<mixed>, foods: ?array<mixed>, member: ?array<mixed>, workouts: ?array<mixed>, diets: ?array<mixed>}
     */
    private function loadPayloads(): array
    {
        File::ensureDirectoryExists($this->importsPath());

        $payloads = [];
        foreach (self::IMPORT_FILES as $key => $fileName) {
            $path = $this->importPath($fileName);
            $payloads[$key] = File::exists($path)
                ? json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR)
                : null;
        }

        if (collect($payloads)->filter()->isEmpty()) {
            $expected = collect(self::IMPORT_FILES)->values()->implode(', ');

            throw new RuntimeException("Nenhum JSON real do MGTEAM encontrado em storage/app/imports. Arquivos esperados: {$expected}.");
        }

        return $payloads;
    }

    private function importsPath(): string
    {
        return storage_path('app/imports');
    }

    private function importPath(string $fileName): string
    {
        return $this->importsPath().DIRECTORY_SEPARATOR.$fileName;
    }

    /**
     * @param  array<string, mixed>  $payloads
     * @return array<string, mixed>
     */
    private function memberPayload(array $payloads): array
    {
        $memberPayload = $payloads['member'] ?? [];

        return $this->firstArray([
            data_get($memberPayload, 'details.0.json.data.customer'),
            data_get($memberPayload, 'target.customer'),
            data_get($memberPayload, 'member'),
            data_get($memberPayload, 'user'),
            data_get($memberPayload, 'profile'),
            $memberPayload,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payloads
     * @return array<int, array<string, mixed>>
     */
    private function exercisePayloads(array $payloads): array
    {
        return $this->mergeRecordSources([
            $payloads['exercises'],
            data_get($payloads, 'member.exercises'),
            data_get($payloads, 'member.exercise_catalog'),
            data_get($payloads, 'member.catalog.exercises'),
        ], ['exercises', 'data', 'items', 'results']);
    }

    /**
     * @param  array<string, mixed>  $payloads
     * @return array<int, array<string, mixed>>
     */
    private function foodPayloads(array $payloads): array
    {
        return $this->mergeRecordSources([
            $payloads['foods'],
            data_get($payloads, 'member.foods'),
            data_get($payloads, 'member.food_catalog'),
            data_get($payloads, 'member.catalog.foods'),
        ], ['foods', 'data', 'items', 'results']);
    }

    /**
     * @param  array<string, mixed>  $payloads
     * @return array<int, array<string, mixed>>
     */
    private function workoutPayloads(array $payloads): array
    {
        return $this->mergeRecordSources([
            $payloads['workouts'],
            data_get($payloads, 'member.workouts'),
            data_get($payloads, 'member.trainings'),
            data_get($payloads, 'member.prescriptions.workouts'),
            data_get($payloads, 'member.treinos'),
        ], ['workouts', 'trainings', 'treinos', 'data', 'items', 'results']);
    }

    /**
     * @param  array<string, mixed>  $payloads
     * @return array<int, array<string, mixed>>
     */
    private function dietPayloads(array $payloads): array
    {
        return $this->mergeRecordSources([
            $payloads['diets'],
            data_get($payloads, 'member.diets'),
            data_get($payloads, 'member.diet_menus'),
            data_get($payloads, 'member.menus'),
            data_get($payloads, 'member.prescriptions.diets'),
            data_get($payloads, 'member.dietas'),
        ], ['diets', 'diet_menus', 'menus', 'dietas', 'data', 'items', 'results']);
    }

    /**
     * @param  array<int, mixed>  $sources
     * @param  array<int, string>  $recordKeys
     * @return array<int, array<string, mixed>>
     */
    private function mergeRecordSources(array $sources, array $recordKeys): array
    {
        return collect($sources)
            ->flatMap(fn ($source) => $this->records($source, $recordKeys))
            ->filter(fn (array $record) => $record !== [])
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $recordKeys
     * @return array<int, array<string, mixed>>
     */
    private function records(mixed $source, array $recordKeys): array
    {
        if (! is_array($source) || $source === []) {
            return [];
        }

        if (array_is_list($source)) {
            return array_values(array_filter($source, is_array(...)));
        }

        foreach ($recordKeys as $key) {
            $value = data_get($source, $key);
            if (is_array($value)) {
                return $this->records($value, $recordKeys);
            }
        }

        return [$source];
    }

    /**
     * @param  array<int, mixed>  $values
     * @return array<string, mixed>
     */
    private function firstArray(array $values): array
    {
        foreach ($values as $value) {
            if (is_array($value) && $value !== []) {
                return $value;
            }
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function upsertMember(User $owner, array $payload): Member
    {
        $email = $this->text($payload, ['email', 'mail', 'contato.email'])
            ?: 'marcelo.guerreiro+mg-import@mgteam.local';

        return Member::withoutGlobalScopes()->updateOrCreate(
            [
                'parent_id' => $owner->id,
                'email' => $email,
            ],
            [
                'name' => $this->text($payload, ['name', 'full_name', 'nome']) ?: 'Marcelo Guerreiro',
                'phone' => $this->text($payload, ['phone', 'telefone', 'cellphone', 'mobile', 'whatsapp']),
                'date_of_birth' => $this->date($payload, ['date_of_birth', 'birth_date', 'birthdate', 'nascimento']),
                'gender' => $this->gender($this->text($payload, ['gender', 'sexo'])),
                'address' => $this->text($payload, ['address', 'endereco']),
                'medical_conditions' => $this->text($payload, ['medical_conditions', 'restrictions', 'observations', 'observacoes']),
                'status' => 'active',
                'coach_user_id' => $owner->id,
            ]
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function upsertExercises(User $owner, array $items): int
    {
        $count = 0;

        foreach ($items as $item) {
            $name = $this->text($item, ['name', 'title', 'exercise_name', 'nome', 'exercise.name']);
            if (! $name) {
                continue;
            }

            $vimeoId = $this->integer($item, ['vimeo_id', 'vimeoId', 'video.vimeo_id']);
            $identity = $vimeoId
                ? ['parent_id' => $owner->id, 'vimeo_id' => $vimeoId]
                : ['parent_id' => $owner->id, 'name' => $name];

            Exercise::withoutGlobalScopes()->updateOrCreate($identity, [
                'name' => $name,
                'vimeo_url' => $this->text($item, ['vimeo_url', 'video_url', 'video.url', 'url']),
                'embed_url' => $this->text($item, ['embed_url', 'embedUrl', 'video.embed_url']),
                'duration_seconds' => $this->integer($item, ['duration_seconds', 'duration', 'video.duration_seconds']),
                'source' => $this->text($item, ['source']) ?: 'mgteam',
            ]);

            $count++;
        }

        return $count;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function upsertFoods(User $owner, array $items): int
    {
        $count = 0;

        foreach ($items as $item) {
            if ($this->upsertFood($owner, $item)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function upsertFood(User $owner, array $item): ?DietFood
    {
        $name = $this->text($item, ['name', 'title', 'food_name', 'nome', 'alimento.name']);
        if (! $name) {
            return null;
        }

        return DietFood::withoutGlobalScopes()->updateOrCreate(
            [
                'parent_id' => $owner->id,
                'name' => $name,
            ],
            [
                'food_group' => $this->text($item, ['food_group', 'group', 'category', 'grupo']),
                'calories' => $this->macroPerCatalogUnit($item, ['calories', 'kcal', 'energia', 'macros.kcal']),
                'protein' => $this->macroPerCatalogUnit($item, ['protein', 'proteins', 'proteina', 'protein_g', 'macros.protein_g']),
                'carbs' => $this->macroPerCatalogUnit($item, ['carbs', 'carbohydrates', 'carboidrato', 'carbo_g', 'carbs_g', 'macros.carbs_g']),
                'fat' => $this->macroPerCatalogUnit($item, ['fat', 'fats', 'gordura', 'fat_g', 'macros.fat_g']),
                'unit' => $this->text($item, ['unit', 'unidade']) ?: '100g',
            ]
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function upsertWorkouts(User $owner, Member $member, array $items): int
    {
        $count = 0;

        foreach ($items as $item) {
            $name = $this->text($item, ['name', 'title', 'nome']) ?: 'Treino MGTEAM';

            $workout = Workout::withoutGlobalScopes()->updateOrCreate(
                [
                    'parent_id' => $owner->id,
                    'member_id' => $member->id,
                    'name' => $name,
                ],
                [
                    'description' => $this->text($item, ['description', 'descricao', 'objective', 'goal']),
                    'workout_date' => $this->date($item, ['workout_date', 'date', 'starts_at', 'data']),
                    'status' => $this->workoutStatus($this->text($item, ['status'])),
                    'notes' => $this->text($item, ['notes', 'observations', 'observacoes']),
                ]
            );

            $workout->activities()->delete();

            foreach ($this->workoutActivities($item) as $index => $activity) {
                $exerciseName = $this->text($activity, ['exercise_name', 'name', 'title', 'nome', 'exercise.name']);
                if (! $exerciseName) {
                    continue;
                }

                $workout->activities()->create([
                    'exercise_name' => $exerciseName,
                    'description' => $this->text($activity, ['description', 'descricao']),
                    'sets' => $this->exerciseSets($activity),
                    'reps' => $this->exerciseReps($activity),
                    'duration_minutes' => $this->integer($activity, ['duration_minutes', 'duration', 'minutos']),
                    'rest_seconds' => $this->restSeconds($activity) ?? 60,
                    'weight_kg' => $this->decimal($activity, ['weight_kg', 'load', 'carga']),
                    'order' => $this->integer($activity, ['order', 'position', 'ordem']) ?? $index,
                    'notes' => $this->workoutActivityNotes($activity),
                ]);
            }

            $count++;
        }

        return $count;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function upsertDiets(User $owner, Member $member, array $items): int
    {
        $count = 0;

        foreach ($items as $item) {
            $name = $this->text($item, ['name', 'title', 'nome']) ?: 'Dieta MGTEAM';

            $menu = DietMenu::withoutGlobalScopes()->updateOrCreate(
                [
                    'parent_id' => $owner->id,
                    'name' => $name,
                ],
                [
                    'status' => $this->dietStatus($this->text($item, ['status'])),
                    'description' => $this->text($item, ['description', 'descricao', 'notes', 'observations']),
                    'meals_count' => 0,
                    'total_calories' => 0,
                ]
            );

            $menu->meals()->delete();

            foreach ($this->dietMeals($item) as $mealIndex => $mealPayload) {
                $meal = $menu->meals()->create([
                    'name' => $this->text($mealPayload, ['name', 'title', 'nome']) ?: 'Refeição '.($mealIndex + 1),
                    'time_label' => $this->text($mealPayload, ['time_label', 'time', 'horario']),
                    'order' => $this->integer($mealPayload, ['order', 'position', 'ordem']) ?? $mealIndex,
                    'notes' => $this->dietMealNotes($mealPayload),
                ]);

                $this->attachMealFoods($owner, $meal, $mealPayload);
            }

            $menu->syncAggregateCounters();

            DietPrescription::withoutGlobalScopes()->updateOrCreate(
                [
                    'parent_id' => $owner->id,
                    'member_id' => $member->id,
                    'diet_menu_id' => $menu->id,
                    'title' => $name,
                ],
                [
                    'notes' => $this->text($item, ['notes', 'observations', 'observacoes']),
                    'status' => 'sent',
                    'delivery_status' => 'DELIVERED',
                    'sent_at' => now(),
                ]
            );

            $count++;
        }

        return $count;
    }

    /**
     * @param  array<string, mixed>  $mealPayload
     */
    private function attachMealFoods(User $owner, DietMeal $meal, array $mealPayload): void
    {
        $foods = $this->records($this->firstArray([
            data_get($mealPayload, 'foods'),
            data_get($mealPayload, 'alimentos'),
            data_get($mealPayload, 'items'),
        ]), ['foods', 'alimentos', 'items']);

        foreach ($foods as $index => $foodPayload) {
            $foodData = $this->firstArray([
                data_get($foodPayload, 'food'),
                data_get($foodPayload, 'alimento'),
                $foodPayload,
            ]);
            $food = $this->upsertFood($owner, $foodData);

            if (! $food) {
                continue;
            }

            $meal->mealFoods()->create([
                'diet_food_id' => $food->id,
                'quantity_in_grams' => $this->mealFoodQuantity($foodPayload),
                'order' => $this->integer($foodPayload, ['order', 'position', 'ordem']) ?? $index,
                'notes' => $this->mealFoodNotes($foodPayload),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $diet
     * @return array<int, array<string, mixed>>
     */
    private function dietMeals(array $diet): array
    {
        $meals = $this->records($this->firstArray([
            data_get($diet, 'meals'),
            data_get($diet, 'refeicoes'),
            data_get($diet, 'items'),
        ]), ['meals', 'refeicoes', 'items']);

        $mainMeals = collect($meals)
            ->filter(fn (array $meal): bool => $this->boolean($meal, ['is_substitute', 'substitute']) !== true)
            ->values();

        if ($mainMeals->isEmpty()) {
            return $meals;
        }

        $substituteMeals = collect($meals)
            ->filter(fn (array $meal): bool => $this->boolean($meal, ['is_substitute', 'substitute']) === true)
            ->values();

        return $mainMeals
            ->map(function (array $meal) use ($substituteMeals): array {
                $meal['substitute_notes'] = $this->substituteMealNotes($meal, $substituteMeals);

                return $meal;
            })
            ->all();
    }

    /**
     * @param  array<string, mixed>  $meal
     */
    private function dietMealNotes(array $meal): ?string
    {
        return $this->compactNotes([
            $this->text($meal, ['notes', 'observations', 'observacoes']),
            $this->text($meal, ['substitute_notes']),
        ]);
    }

    /**
     * @param  array<string, mixed>  $mainMeal
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $substituteMeals
     */
    private function substituteMealNotes(array $mainMeal, \Illuminate\Support\Collection $substituteMeals): ?string
    {
        $mainName = $this->text($mainMeal, ['name', 'title', 'nome']);
        $mainTime = $this->text($mainMeal, ['time_label', 'time', 'horario']);

        $alternatives = $substituteMeals
            ->filter(fn (array $substitute): bool => $this->text($substitute, ['name', 'title', 'nome']) === $mainName
                && $this->text($substitute, ['time_label', 'time', 'horario']) === $mainTime)
            ->values()
            ->map(function (array $substitute, int $index): string {
                $foods = collect($this->records($this->firstArray([
                    data_get($substitute, 'foods'),
                    data_get($substitute, 'alimentos'),
                    data_get($substitute, 'items'),
                ]), ['foods', 'alimentos', 'items']))
                    ->map(function (array $food): string {
                        $name = $this->text($food, ['name', 'title', 'food_name', 'nome', 'food.name']) ?: 'Alimento';
                        $quantity = $this->decimal($food, ['quantity', 'quantity_in_grams', 'grams', 'gramas', 'qtd']);
                        $unit = $this->text($food, ['unit', 'unidade']) ?: 'g';

                        return $quantity > 0 ? "{$name} ({$quantity} {$unit})" : $name;
                    })
                    ->implode(', ');

                return 'Opção '.($index + 2).': '.$foods;
            });

        return $alternatives->isEmpty()
            ? null
            : "Alternativas MGTEAM:\n".$alternatives->implode("\n");
    }

    /**
     * @param  array<string, mixed>  $workout
     * @return array<int, array<string, mixed>>
     */
    private function workoutActivities(array $workout): array
    {
        $activities = $this->records($this->firstArray([
            data_get($workout, 'activities'),
            data_get($workout, 'exercises'),
            data_get($workout, 'items'),
            data_get($workout, 'exercicios'),
        ]), ['activities', 'exercises', 'items', 'exercicios']);

        $divisions = $this->records(data_get($workout, 'divisions'), ['divisions', 'data', 'items']);
        foreach ($divisions as $divisionIndex => $division) {
            $divisionLabel = $this->text($division, ['label', 'name', 'title', 'nome']);
            $divisionDescription = $divisionLabel ? "Divisão {$divisionLabel}" : null;
            $divisionExercises = $this->records($this->firstArray([
                data_get($division, 'exercises'),
                data_get($division, 'activities'),
                data_get($division, 'items'),
                data_get($division, 'exercicios'),
            ]), ['exercises', 'activities', 'items', 'exercicios']);

            foreach ($divisionExercises as $exerciseIndex => $exercise) {
                $exercise['description'] = $this->text($exercise, ['description', 'descricao']) ?: $divisionDescription;
                $exercise['division_label'] = $divisionLabel;
                $exercise['division_notes'] = $this->text($division, ['notes', 'observations', 'observacoes']);
                $exercise['order'] = $this->integer($exercise, ['order', 'position', 'ordem']) ?? (($divisionIndex + 1) * 100 + $exerciseIndex);

                $activities[] = $exercise;
            }
        }

        return $activities;
    }

    /**
     * @param  array<string, mixed>  $activity
     */
    private function exerciseSets(array $activity): ?int
    {
        $sets = $this->integer($activity, ['sets', 'series', 'series_count']);
        if ($sets !== null) {
            return $sets;
        }

        $rawSets = $this->text($activity, ['sets', 'series']);
        if (! $rawSets || ! preg_match_all('/(\d+)\s*x\s*\d+/iu', $rawSets, $matches)) {
            return null;
        }

        return (int) array_sum(array_map('intval', $matches[1]));
    }

    /**
     * @param  array<string, mixed>  $activity
     */
    private function exerciseReps(array $activity): ?int
    {
        $reps = $this->integer($activity, ['reps', 'repetitions', 'repeticoes']);
        if ($reps !== null) {
            return $reps;
        }

        $rawSets = $this->text($activity, ['sets', 'series']);
        if (! $rawSets || ! preg_match('/\d+\s*x\s*(\d+)/iu', $rawSets, $matches)) {
            return null;
        }

        return (int) $matches[1];
    }

    /**
     * @param  array<string, mixed>  $activity
     */
    private function restSeconds(array $activity): ?int
    {
        $rest = $this->integer($activity, ['rest_seconds', 'descanso']);
        if ($rest !== null) {
            return $rest;
        }

        $rawRest = $this->text($activity, ['rest', 'interval']);
        if (! $rawRest || ! preg_match('/(\d+)/', $rawRest, $matches)) {
            return null;
        }

        $seconds = (int) $matches[1];

        return str_contains(strtolower($rawRest), 'min') ? $seconds * 60 : $seconds;
    }

    /**
     * @param  array<string, mixed>  $activity
     */
    private function workoutActivityNotes(array $activity): ?string
    {
        return $this->compactNotes([
            $this->text($activity, ['division_notes']),
            $this->text($activity, ['sets', 'series']) ? 'Séries: '.$this->text($activity, ['sets', 'series']) : null,
            $this->text($activity, ['rest', 'interval']) ? 'Descanso: '.$this->text($activity, ['rest', 'interval']) : null,
            $this->text($activity, ['notes', 'observations', 'observacoes']),
            $this->text($activity, ['video_url', 'vimeo_url', 'embed_url']),
        ]);
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<int, string>  $keys
     */
    private function macroPerCatalogUnit(array $item, array $keys): float
    {
        $macro = $this->decimal($item, $keys);
        $quantity = $this->decimal($item, ['quantity_in_grams', 'grams', 'quantity', 'gramas', 'qtd']);

        if ($macro <= 0 || $quantity <= 0) {
            return $macro;
        }

        if ($this->isGramUnit($this->text($item, ['unit', 'unidade']))) {
            return round(($macro / $quantity) * 100, 2);
        }

        return round($macro / $quantity, 2);
    }

    /**
     * @param  array<string, mixed>  $foodPayload
     */
    private function mealFoodQuantity(array $foodPayload): float
    {
        $quantity = $this->decimal($foodPayload, ['quantity_in_grams', 'grams', 'quantity', 'gramas', 'qtd']);
        if ($quantity <= 0) {
            return 100;
        }

        return $this->isGramUnit($this->text($foodPayload, ['unit', 'unidade']))
            ? $quantity
            : $quantity * 100;
    }

    /**
     * @param  array<string, mixed>  $foodPayload
     */
    private function mealFoodNotes(array $foodPayload): ?string
    {
        $unit = $this->text($foodPayload, ['unit', 'unidade']);
        $quantity = $this->decimal($foodPayload, ['quantity', 'qtd']);

        return $this->compactNotes([
            $this->text($foodPayload, ['notes', 'observations', 'observacoes']),
            $unit && ! $this->isGramUnit($unit) && $quantity > 0 ? "Quantidade MGTEAM: {$quantity} {$unit}" : null,
        ]);
    }

    private function isGramUnit(?string $unit): bool
    {
        $normalized = strtolower((string) $unit);

        return $normalized === ''
            || str_contains($normalized, 'gram')
            || in_array($normalized, ['g', 'gr', '100g'], true);
    }

    /**
     * @param  array<int, ?string>  $notes
     */
    private function compactNotes(array $notes): ?string
    {
        $cleanNotes = collect($notes)
            ->filter(fn (?string $note) => filled($note))
            ->unique()
            ->values();

        return $cleanNotes->isEmpty() ? null : $cleanNotes->implode(PHP_EOL);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, string>  $keys
     */
    private function text(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = data_get($payload, $key);
            if ($value !== null && $value !== '') {
                if (is_array($value) || is_object($value)) {
                    continue;
                }

                return trim((string) $value);
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, string>  $keys
     */
    private function integer(array $payload, array $keys): ?int
    {
        $value = $this->text($payload, $keys);

        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, string>  $keys
     */
    private function boolean(array $payload, array $keys): ?bool
    {
        foreach ($keys as $key) {
            $value = data_get($payload, $key);

            if (is_bool($value)) {
                return $value;
            }

            if ($value === null || $value === '') {
                continue;
            }

            return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, string>  $keys
     */
    private function decimal(array $payload, array $keys): float
    {
        $value = $this->text($payload, $keys);

        if (! is_numeric($value)) {
            return 0.0;
        }

        return round((float) $value, 2);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, string>  $keys
     */
    private function date(array $payload, array $keys): ?string
    {
        $value = $this->text($payload, $keys);

        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function gender(?string $value): ?string
    {
        return match (strtolower((string) $value)) {
            'male', 'm', 'masculino' => 'male',
            'female', 'f', 'feminino' => 'female',
            'other', 'outro', 'outros' => 'other',
            default => null,
        };
    }

    private function workoutStatus(?string $value): string
    {
        return match (strtolower((string) $value)) {
            'completed', 'concluido', 'concluído' => 'completed',
            'cancelled', 'canceled', 'cancelado' => 'cancelled',
            default => 'active',
        };
    }

    private function dietStatus(?string $value): string
    {
        return match (strtolower((string) $value)) {
            'draft', 'rascunho' => 'draft',
            default => 'published',
        };
    }
}
