<?php

use App\Models\ClientFeedback;
use App\Models\CommunityGroup;
use App\Models\CommunityPost;
use App\Models\DietFood;
use App\Models\DietMenu;
use App\Models\DietPrescription;
use App\Models\MemberLogbook;
use App\Models\MemberPhoto;
use App\Models\Workout;
use App\Models\WorkoutActivity;
use App\Models\WorkoutActivityLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

describe('Mobile student prescription API', function () {
    it('returns workouts with progress and diets with meals, foods and macros', function () {
        $owner = createOwner(['email' => 'mobile.rx.owner@test.app']);
        $member = createMemberFor($owner, [
            'name' => 'Ana Beatriz',
            'email' => 'ana.mobile.rx@test.app',
        ]);

        $workout = Workout::query()->create([
            'parent_id' => $owner->id,
            'member_id' => $member->id,
            'name' => 'Treino A — Inferiores',
            'description' => 'Foco em força e hipertrofia',
            'status' => 'active',
            'notes' => 'Aumentar carga se a técnica estiver limpa.',
        ]);

        WorkoutActivity::query()->create([
            'workout_id' => $workout->id,
            'exercise_name' => 'Agachamento livre',
            'sets' => 4,
            'reps' => 10,
            'weight_kg' => 40,
            'rest_seconds' => 90,
            'order' => 1,
            'is_completed' => true,
        ]);

        WorkoutActivity::query()->create([
            'workout_id' => $workout->id,
            'exercise_name' => 'Cadeira extensora',
            'sets' => 3,
            'reps' => 12,
            'weight_kg' => 25,
            'rest_seconds' => 60,
            'order' => 2,
            'is_completed' => false,
        ]);

        $menu = DietMenu::query()->create([
            'parent_id' => $owner->id,
            'name' => 'Cutting Fase 1',
            'status' => 'published',
            'meals_count' => 0,
            'total_calories' => 0,
            'description' => 'Plano com alto teor de proteína.',
        ]);

        $frango = DietFood::query()->create([
            'parent_id' => $owner->id,
            'name' => 'Frango grelhado',
            'food_group' => 'Proteína',
            'calories' => 165,
            'protein' => 31,
            'carbs' => 0,
            'fat' => 3.6,
            'unit' => '100g',
        ]);
        $arroz = DietFood::query()->create([
            'parent_id' => $owner->id,
            'name' => 'Arroz cozido',
            'food_group' => 'Carboidrato',
            'calories' => 130,
            'protein' => 2.7,
            'carbs' => 28,
            'fat' => 0.3,
            'unit' => '100g',
        ]);

        $meal = $menu->meals()->create([
            'name' => 'Almoço',
            'time_label' => '12:00',
            'order' => 1,
            'notes' => 'Pode trocar salada à vontade.',
        ]);
        $meal->mealFoods()->create([
            'diet_food_id' => $frango->id,
            'quantity_in_grams' => 150,
            'order' => 1,
        ]);
        $meal->mealFoods()->create([
            'diet_food_id' => $arroz->id,
            'quantity_in_grams' => 100,
            'order' => 2,
        ]);
        $menu->syncAggregateCounters();

        DietPrescription::query()->create([
            'parent_id' => $owner->id,
            'member_id' => $member->id,
            'diet_menu_id' => $menu->id,
            'title' => 'Dieta Cutting Fase 1',
            'notes' => 'Beber 3L de água.',
            'status' => 'sent',
            'delivery_status' => 'DELIVERED',
            'scheduled_at' => now(),
            'sent_at' => now(),
        ]);

        $login = $this->postJson('/api/auth/login', [
            'email' => 'ana.mobile.rx@test.app',
            'password' => 'password',
            'device_name' => 'pest-mobile',
        ])->assertOk();

        $this->withToken($login->json('token'))
            ->getJson('/api/prescriptions')
            ->assertOk()
            ->assertJsonPath('workouts.0.name', 'Treino A — Inferiores')
            ->assertJsonPath('workouts.0.activities_total', 2)
            ->assertJsonPath('workouts.0.activities_completed', 1)
            ->assertJsonPath('workouts.0.completion_percentage', 50)
            ->assertJsonPath('workouts.0.activities.0.exercise_name', 'Agachamento livre')
            ->assertJsonPath('workouts.0.activities.0.logs', [])
            ->assertJsonPath('workouts.0.activities.0.details', '4 sets × 10 reps | 40.00 kg')
            ->assertJsonPath('diets.0.title', 'Dieta Cutting Fase 1')
            ->assertJsonPath('diets.0.diet_menu.name', 'Cutting Fase 1')
            ->assertJsonPath('diets.0.diet_menu.meals_count', 1)
            ->assertJsonPath('diets.0.diet_menu.macros.calories', 377.5)
            ->assertJsonPath('diets.0.diet_menu.meals.0.name', 'Almoço')
            ->assertJsonPath('diets.0.diet_menu.meals.0.foods.0.name', 'Frango grelhado')
            ->assertJsonPath('diets.0.diet_menu.meals.0.foods.0.macros.protein', 46.5);
    });

    it('logs workout activity and returns updated progress for the mobile app', function () {
        $owner = createOwner(['email' => 'mobile.workout.owner@test.app']);
        $member = createMemberFor($owner, ['email' => 'ana.mobile.workout@test.app']);
        $workout = Workout::query()->create([
            'parent_id' => $owner->id,
            'member_id' => $member->id,
            'name' => 'Treino de check-in',
            'status' => 'active',
        ]);
        $activity = WorkoutActivity::query()->create([
            'workout_id' => $workout->id,
            'exercise_name' => 'Supino reto',
            'sets' => 3,
            'reps' => 8,
            'weight_kg' => 30,
            'order' => 1,
            'is_completed' => false,
        ]);

        $login = $this->postJson('/api/auth/login', [
            'email' => 'ana.mobile.workout@test.app',
            'password' => 'password',
        ])->assertOk();

        $this->withToken($login->json('token'))
            ->postJson("/api/workouts/{$workout->id}/activities/{$activity->id}/log", [
                'is_completed' => false,
                'sets' => 3,
                'reps' => 8,
                'weight_kg' => 31,
                'notes' => 'Série parcial registrada.',
            ])
            ->assertCreated()
            ->assertJsonPath('workout.activities_total', 1)
            ->assertJsonPath('workout.activities_completed', 0)
            ->assertJsonPath('workout.completion_percentage', 0)
            ->assertJsonPath('workout.activities.0.is_completed', false)
            ->assertJsonPath('workout.activities.0.weight_kg', 31)
            ->assertJsonPath('workout.activities.0.logs.0.is_completed', false)
            ->assertJsonPath('workout.activities.0.logs.0.notes', 'Série parcial registrada.');

        $this->withToken($login->json('token'))
            ->postJson("/api/workouts/{$workout->id}/activities/{$activity->id}/log", [
                'is_completed' => true,
                'sets' => 4,
                'reps' => 9,
                'weight_kg' => 32.5,
                'notes' => 'Boa execução.',
            ])
            ->assertCreated()
            ->assertJsonPath('workout.activities_total', 1)
            ->assertJsonPath('workout.activities_completed', 1)
            ->assertJsonPath('workout.completion_percentage', 100)
            ->assertJsonPath('workout.activities.0.is_completed', true)
            ->assertJsonPath('workout.activities.0.weight_kg', 32.5)
            ->assertJsonPath('workout.activities.0.logs.0.is_completed', true)
            ->assertJsonPath('workout.activities.0.logs.1.is_completed', false);

        $this->withToken($login->json('token'))
            ->postJson("/api/workouts/{$workout->id}/complete", [
                'rating' => 4,
                'comment' => 'Treino bom, última série pesada.',
                'duration_seconds' => 1845,
            ])
            ->assertOk()
            ->assertJsonPath('workout.status', 'completed')
            ->assertJsonPath('logbook.type', 'TRAINING')
            ->assertJsonPath('logbook.rating', 4)
            ->assertJsonPath('logbook.metadata.source', 'student_workout_complete')
            ->assertJsonPath('logbook.metadata.duration_seconds', 1845);

        $this->withToken($login->json('token'))
            ->postJson("/api/workouts/{$workout->id}/complete", [
                'rating' => 2,
                'comment' => 'Toque duplicado.',
            ])
            ->assertOk()
            ->assertJsonPath('workout.status', 'completed')
            ->assertJsonPath('logbook.rating', 4);

        $logbook = MemberLogbook::query()
            ->where('member_id', $member->id)
            ->where('type', 'TRAINING')
            ->first();

        expect($logbook)->not->toBeNull()
            ->and($logbook->rating)->toBe(4)
            ->and($logbook->comment)->toBe('Treino bom, última série pesada.')
            ->and($logbook->metadata['source'])->toBe('student_workout_complete')
            ->and($logbook->metadata['duration_seconds'])->toBe(1845)
            ->and(MemberLogbook::query()
                ->where('member_id', $member->id)
                ->where('type', 'TRAINING')
                ->count())->toBe(1);

        expect(WorkoutActivityLog::query()
            ->where('workout_activity_id', $activity->id)
            ->count())->toBe(2);

        $this->withToken($login->json('token'))
            ->deleteJson("/api/workouts/{$workout->id}/activities/{$activity->id}/log")
            ->assertOk()
            ->assertJsonPath('workout.status', 'active')
            ->assertJsonPath('workout.activities_completed', 0)
            ->assertJsonPath('workout.completion_percentage', 0)
            ->assertJsonPath('workout.activities.0.is_completed', false)
            ->assertJsonPath('workout.activities.0.logs.0.is_completed', false);

        expect(MemberLogbook::query()
            ->where('member_id', $member->id)
            ->where('type', 'TRAINING')
            ->where('metadata->source', 'student_workout_complete')
            ->where('metadata->workout_id', $workout->id)
            ->count())->toBe(0);
    });

    it('stores mobile diet logbooks with meal metadata', function () {
        $owner = createOwner(['email' => 'mobile.logbook.owner@test.app']);
        $member = createMemberFor($owner, ['email' => 'ana.mobile.logbook@test.app']);

        $login = $this->postJson('/api/auth/login', [
            'email' => 'ana.mobile.logbook@test.app',
            'password' => 'password',
        ])->assertOk();

        $this->withToken($login->json('token'))
            ->postJson('/api/logbooks', [
                'type' => 'DIET',
                'title' => 'Refeição concluída: Almoço',
                'date' => now()->toDateString(),
                'numeric_value' => 377.5,
                'unit' => 'kcal',
                'metadata' => [
                    'source' => 'student_diet_detail_meal',
                    'prescription_id' => 10,
                    'meal_id' => 20,
                    'meal_name' => 'Almoço',
                ],
                'comment' => 'Refeição marcada pelo app do aluno.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.type', 'DIET')
            ->assertJsonPath('data.metadata.meal_id', 20);

        $logbook = MemberLogbook::query()
            ->where('member_id', $member->id)
            ->where('type', 'DIET')
            ->first();

        expect($logbook)->not->toBeNull()
            ->and($logbook->metadata['meal_name'])->toBe('Almoço')
            ->and((float) $logbook->numeric_value)->toBe(377.5)
            ->and($logbook->unit)->toBe('kcal');

        $this->withToken($login->json('token'))
            ->postJson('/api/logbooks', [
                'type' => 'DIET',
                'title' => 'Jantar fora',
                'logged_at' => now()->toISOString(),
                'numeric_value' => 640,
                'unit' => 'kcal',
                'metadata' => [
                    'source' => 'student_diet_free_meal',
                    'meal_name' => 'Jantar fora',
                ],
                'comment' => 'Comi fora do plano.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.type', 'DIET')
            ->assertJsonPath('data.metadata.source', 'student_diet_free_meal')
            ->assertJsonPath('data.numeric_value', '640.00');

        $this->withToken($login->json('token'))
            ->postJson('/api/logbooks', [
                'type' => 'WEIGHT',
                'title' => 'Peso corporal',
                'logged_at' => now()->toISOString(),
                'numeric_value' => 72.5,
                'unit' => 'kg',
                'metadata' => [
                    'source' => 'student_weight_quick_log',
                ],
                'comment' => 'Peso em jejum.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.type', 'WEIGHT')
            ->assertJsonPath('data.metadata.source', 'student_weight_quick_log')
            ->assertJsonPath('data.numeric_value', '72.50')
            ->assertJsonPath('data.unit', 'kg');

        $this->actingAs($owner)
            ->get(route('members.logbook', ['type' => 'DIET']))
            ->assertOk()
            ->assertSee('Jantar fora')
            ->assertSee('640,00 kcal')
            ->assertSee('Refeição livre');

        $this->actingAs($owner)
            ->get(route('members.logbook', ['type' => 'WEIGHT']))
            ->assertOk()
            ->assertSee('Peso corporal')
            ->assertSee('72,50 kg')
            ->assertSee('Peso no app');
    });

    it('stores contextual feedback for the authenticated student', function () {
        $owner = createOwner(['email' => 'mobile.feedback.owner@test.app']);
        $member = createMemberFor($owner, ['email' => 'ana.mobile.feedback@test.app']);

        $login = $this->postJson('/api/auth/login', [
            'email' => 'ana.mobile.feedback@test.app',
            'password' => 'password',
        ])->assertOk();

        $this->withToken($login->json('token'))
            ->postJson('/api/feedbacks', [
                'message' => 'Senti dor no joelho no agachamento.',
                'rating' => 2,
                'context_type' => 'workout',
                'context_id' => 123,
            ])
            ->assertCreated()
            ->assertJsonPath('data.member_id', $member->id)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.rating', 2)
            ->assertJsonPath('data.context_type', 'workout')
            ->assertJsonPath('data.context_id', 123)
            ->assertJsonPath('data.message', '[WORKOUT #123] Senti dor no joelho no agachamento.');

        $this->withToken($login->json('token'))
            ->postJson('/api/feedbacks', [
                'message' => 'Nao consegui bater a refeicao no horario.',
                'context_type' => 'meal',
                'context_id' => 456,
            ])
            ->assertCreated()
            ->assertJsonPath('data.rating', null)
            ->assertJsonPath('data.context_type', 'meal')
            ->assertJsonPath('data.context_id', 456)
            ->assertJsonPath('data.message', '[MEAL #456] Nao consegui bater a refeicao no horario.');

        expect(ClientFeedback::query()
            ->where('parent_id', $owner->id)
            ->where('member_id', $member->id)
            ->where('status', 'pending')
            ->count())->toBe(2);

        $this->actingAs($owner)
            ->get(route('feedbacks.index'))
            ->assertOk()
            ->assertSee('Treino #123')
            ->assertSee('Refeição #456');

        $this->withToken($login->json('token'))
            ->postJson('/api/feedbacks', [
                'message' => '',
                'rating' => 6,
                'context_type' => 'invalid',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['message', 'rating', 'context_type']);
    });

    it('uploads and lists progress photos for the authenticated student', function () {
        Storage::fake('public');

        $owner = createOwner(['email' => 'mobile.photos.owner@test.app']);
        $member = createMemberFor($owner, ['email' => 'ana.mobile.photos@test.app']);

        $login = $this->postJson('/api/auth/login', [
            'email' => 'ana.mobile.photos@test.app',
            'password' => 'password',
        ])->assertOk();

        $photoFile = UploadedFile::fake()->create('evolucao-julho.jpg', 128, 'image/jpeg');

        $upload = $this->withToken($login->json('token'))
            ->withHeader('Accept', 'application/json')
            ->post('/api/photos', [
                'photo' => $photoFile,
                'type' => 'front',
                'caption' => 'Check-in Julho',
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Foto de evolucao enviada.')
            ->assertJsonPath('data.member_id', $member->id)
            ->assertJsonPath('data.type', 'front')
            ->assertJsonPath('data.caption', 'Check-in Julho')
            ->assertJsonStructure([
                'data' => ['id', 'path', 'url', 'type', 'caption', 'created_at'],
            ]);

        $photo = MemberPhoto::query()
            ->where('parent_id', $owner->id)
            ->where('member_id', $member->id)
            ->first();

        expect($photo)->not->toBeNull()
            ->and($upload->json('data.url'))->toContain('/storage/'.$photo->path);

        Storage::disk('public')->assertExists($photo->path);

        $this->withToken($login->json('token'))
            ->getJson('/api/photos')
            ->assertOk()
            ->assertJsonPath('0.id', $photo->id)
            ->assertJsonPath('0.type', 'front')
            ->assertJsonPath('0.caption', 'Check-in Julho')
            ->assertJsonPath('0.url', $upload->json('data.url'));

        $this->withToken($login->json('token'))
            ->withHeader('Accept', 'application/json')
            ->post('/api/photos', [
                'photo' => UploadedFile::fake()->create('exame.pdf', 10, 'application/pdf'),
                'type' => 'document',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['photo', 'type']);
    });

    it('lists community groups and lets the authenticated student publish posts', function () {
        $owner = createOwner(['email' => 'mobile.community.owner@test.app']);
        $member = createMemberFor($owner, [
            'name' => 'Ana Comunidade',
            'email' => 'ana.mobile.community@test.app',
        ]);

        $group = CommunityGroup::query()->create([
            'parent_id' => $owner->id,
            'name' => 'Desafio 30 dias',
            'description' => 'Grupo de check-ins e apoio diario.',
            'members_count' => 12,
        ]);

        CommunityPost::query()->create([
            'parent_id' => $owner->id,
            'community_group_id' => $group->id,
            'content' => 'Lembrete do coach para registrar o treino.',
            'likes_count' => 3,
        ]);

        $login = $this->postJson('/api/auth/login', [
            'email' => 'ana.mobile.community@test.app',
            'password' => 'password',
        ])->assertOk();

        $this->withToken($login->json('token'))
            ->getJson('/api/groups')
            ->assertOk()
            ->assertJsonPath('groups.0.id', $group->id)
            ->assertJsonPath('groups.0.name', 'Desafio 30 dias')
            ->assertJsonPath('groups.0.posts_count', 1)
            ->assertJsonPath('groups.0.posts.0.author_name', 'Coach')
            ->assertJsonPath('recent_posts.0.content', 'Lembrete do coach para registrar o treino.');

        $post = $this->withToken($login->json('token'))
            ->postJson("/api/groups/{$group->id}/posts", [
                'content' => 'Treino concluido hoje. Bora!',
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Publicacao enviada para a comunidade.')
            ->assertJsonPath('data.group_id', $group->id)
            ->assertJsonPath('data.member_id', $member->id)
            ->assertJsonPath('data.author_name', 'Ana Comunidade')
            ->assertJsonPath('data.content', 'Treino concluido hoje. Bora!');

        expect(CommunityPost::query()
            ->where('parent_id', $owner->id)
            ->where('community_group_id', $group->id)
            ->where('member_id', $member->id)
            ->where('content', $post->json('data.content'))
            ->exists())->toBeTrue();

        $this->withToken($login->json('token'))
            ->postJson("/api/groups/{$group->id}/posts", ['content' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['content']);
    });

    it('completes a diet meal idempotently for the authenticated student', function () {
        $owner = createOwner(['email' => 'mobile.meal.owner@test.app']);
        $member = createMemberFor($owner, ['email' => 'ana.mobile.meal@test.app']);

        $menu = DietMenu::query()->create([
            'parent_id' => $owner->id,
            'name' => 'Plano idempotente',
            'status' => 'published',
            'meals_count' => 0,
            'total_calories' => 0,
        ]);
        $food = DietFood::query()->create([
            'parent_id' => $owner->id,
            'name' => 'Batata doce',
            'food_group' => 'Carboidrato',
            'calories' => 86,
            'protein' => 1.6,
            'carbs' => 20.1,
            'fat' => 0.1,
            'unit' => '100g',
        ]);
        $meal = $menu->meals()->create([
            'name' => 'Pré-treino',
            'time_label' => '16:30',
            'order' => 1,
        ]);
        $meal->mealFoods()->create([
            'diet_food_id' => $food->id,
            'quantity_in_grams' => 200,
            'order' => 1,
        ]);
        $menu->syncAggregateCounters();

        $prescription = DietPrescription::query()->create([
            'parent_id' => $owner->id,
            'member_id' => $member->id,
            'diet_menu_id' => $menu->id,
            'title' => 'Dieta idempotente',
            'status' => 'sent',
            'delivery_status' => 'DELIVERED',
            'sent_at' => now(),
        ]);

        $login = $this->postJson('/api/auth/login', [
            'email' => 'ana.mobile.meal@test.app',
            'password' => 'password',
        ])->assertOk();

        $this->travelTo(now()->setTime(14, 35));

        $this->withToken($login->json('token'))
            ->postJson("/api/diets/{$prescription->id}/meals/{$meal->id}/complete")
            ->assertCreated()
            ->assertJsonPath('created', true)
            ->assertJsonPath('data.type', 'DIET')
            ->assertJsonPath('data.metadata.source', 'student_diet_meal_complete')
            ->assertJsonPath('data.metadata.meal_id', $meal->id)
            ->assertJsonPath('data.numeric_value', '172.00');

        $this->withToken($login->json('token'))
            ->postJson("/api/diets/{$prescription->id}/meals/{$meal->id}/complete")
            ->assertOk()
            ->assertJsonPath('created', false);

        expect(MemberLogbook::query()
            ->where('member_id', $member->id)
            ->where('type', 'DIET')
            ->where('metadata->source', 'student_diet_meal_complete')
            ->where('metadata->meal_id', $meal->id)
            ->count())->toBe(1);

        $mealLogbook = MemberLogbook::query()
            ->where('member_id', $member->id)
            ->where('type', 'DIET')
            ->where('metadata->source', 'student_diet_meal_complete')
            ->where('metadata->meal_id', $meal->id)
            ->first();

        expect($mealLogbook?->logged_at->format('H:i'))->toBe('14:35');

        $this->withToken($login->json('token'))
            ->deleteJson("/api/diets/{$prescription->id}/meals/{$meal->id}/complete")
            ->assertOk()
            ->assertJsonPath('deleted', 1)
            ->assertJsonPath('meal_id', $meal->id)
            ->assertJsonPath('prescription_id', $prescription->id);

        expect(MemberLogbook::query()
            ->where('member_id', $member->id)
            ->where('type', 'DIET')
            ->where('metadata->source', 'student_diet_meal_complete')
            ->where('metadata->meal_id', $meal->id)
            ->count())->toBe(0);

        $this->withToken($login->json('token'))
            ->deleteJson("/api/diets/{$prescription->id}/meals/{$meal->id}/complete")
            ->assertOk()
            ->assertJsonPath('deleted', 0);

        $printLinkResponse = $this->withToken($login->json('token'))
            ->getJson("/api/diets/{$prescription->id}/print-link")
            ->assertOk()
            ->assertJsonStructure(['url', 'expires_at']);

        $printUrl = $printLinkResponse->json('url');
        $printPath = parse_url($printUrl, PHP_URL_PATH);
        $printQuery = parse_url($printUrl, PHP_URL_QUERY);

        expect($printPath)->toBeString()
            ->and($printQuery)->toBeString();

        $this->get($printPath.'?'.$printQuery)
            ->assertOk()
            ->assertSee('Dieta idempotente')
            ->assertSee('Plano idempotente');
    });
});
