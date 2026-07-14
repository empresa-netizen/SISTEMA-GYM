<?php

use App\Models\DietFood;
use App\Models\DietMenu;
use App\Models\DietPrescription;
use App\Models\LibraryWorkout;
use App\Models\LibraryWorkoutActivity;
use App\Models\Workout;
use App\Models\WorkoutActivity;
use App\Services\LibraryWorkoutAssigner;

describe('Prescription flow business logic', function () {
    it('deep clones library workout templates including exercises', function () {
        $owner = createOwner(['email' => 'rx.owner@test.app']);
        $member = createMemberFor($owner, [
            'name' => 'Aluno Prescrição',
            'email' => 'aluno.rx@test.app',
            'status' => 'active',
        ]);

        $template = LibraryWorkout::query()->create([
            'parent_id' => $owner->id,
            'title' => 'Template Hipertrofia A',
            'focus' => 'Full body',
            'duration_weeks' => 4,
            'sessions_per_week' => 3,
            'level' => 'intermediate',
            'status' => 'published',
            'description' => 'Base da biblioteca',
            'notes' => 'Nota do template',
        ]);

        LibraryWorkoutActivity::query()->create([
            'library_workout_id' => $template->id,
            'exercise_name' => 'Agachamento',
            'sets' => 4,
            'reps' => 10,
            'weight_kg' => 60,
            'rest_seconds' => 90,
            'order' => 0,
        ]);
        LibraryWorkoutActivity::query()->create([
            'library_workout_id' => $template->id,
            'exercise_name' => 'Supino reto',
            'sets' => 3,
            'reps' => 8,
            'weight_kg' => 40,
            'rest_seconds' => 75,
            'order' => 1,
        ]);

        $workout = app(LibraryWorkoutAssigner::class)->assign($template, $member);

        expect($workout)->toBeInstanceOf(Workout::class)
            ->and($workout->member_id)->toBe($member->id)
            ->and($workout->parent_id)->toBe($owner->id)
            ->and($workout->name)->toBe('Template Hipertrofia A')
            ->and($workout->notes)->toContain('Importado da biblioteca #'.$template->id)
            ->and($workout->activities)->toHaveCount(2)
            ->and($workout->activities->pluck('exercise_name')->all())->toBe(['Agachamento', 'Supino reto'])
            ->and((int) $workout->activities->first()->sets)->toBe(4)
            ->and((float) $workout->activities->first()->weight_kg)->toBe(60.0);

        expect($workout->activities->first())->toBeInstanceOf(WorkoutActivity::class)
            ->and($template->activities()->first())->toBeInstanceOf(LibraryWorkoutActivity::class)
            ->and(WorkoutActivity::query()->where('workout_id', $workout->id)->count())->toBe(2);

        // Editar o template NÃO altera a ficha já prescrita
        $template->update(['title' => 'Template ALTERADO NA BIBLIOTECA']);
        $template->activities()->first()->update(['exercise_name' => 'Agachamento ALTERADO', 'sets' => 99]);

        $fresh = $workout->fresh()->load('activities');

        expect($fresh->name)->toBe('Template Hipertrofia A')
            ->and($fresh->name)->not->toBe($template->fresh()->title)
            ->and($fresh->activities->first()->exercise_name)->toBe('Agachamento')
            ->and((int) $fresh->activities->first()->sets)->toBe(4)
            ->and(Workout::withoutGlobalScopes()->where('member_id', $member->id)->count())->toBe(1);
    });

    it('forbids assigning a template from another tenant', function () {
        $ownerA = createOwner(['email' => 'rx.a@test.app']);
        $ownerB = createOwner(['email' => 'rx.b@test.app']);
        $memberB = createMemberFor($ownerB, ['email' => 'aluno.b.rx@test.app']);

        $templateA = LibraryWorkout::query()->create([
            'parent_id' => $ownerA->id,
            'title' => 'Template Privado A',
            'status' => 'published',
            'level' => 'beginner',
        ]);

        expect(fn () => app(LibraryWorkoutAssigner::class)->assign($templateA, $memberB))
            ->toThrow(\Symfony\Component\HttpKernel\Exception\HttpException::class);
    });

    it('imports a library workout from the member profile and shows saved exercises', function () {
        $owner = createOwner(['email' => 'rx.web.import@test.app']);
        $member = createMemberFor($owner, [
            'name' => 'Aluno Importação Web',
            'email' => 'aluno.import.web@test.app',
            'status' => 'active',
        ]);

        $template = LibraryWorkout::query()->create([
            'parent_id' => $owner->id,
            'title' => 'Template Web Pernas',
            'focus' => 'Inferiores',
            'status' => 'published',
            'level' => 'intermediate',
            'description' => 'Treino de biblioteca para importar',
        ]);
        LibraryWorkoutActivity::query()->create([
            'library_workout_id' => $template->id,
            'exercise_name' => 'Leg press',
            'sets' => 4,
            'reps' => 12,
            'rest_seconds' => 90,
            'order' => 0,
        ]);

        $this->actingAs($owner)
            ->get(route('members.show', ['member' => $member, 'tab' => 'workout']))
            ->assertOk()
            ->assertSee('prime-rx--workout', false)
            ->assertSee('prime-rx-empty', false)
            ->assertSee('Importar modelo')
            ->assertSee('Novo plano de treino')
            ->assertSee('Template Web Pernas');

        $this->actingAs($owner)
            ->get(route('members.workouts', $member))
            ->assertOk()
            ->assertSee('prime-rx--workout', false)
            ->assertSee('Importar modelo')
            ->assertSee('Novo plano de treino');

        $this->actingAs($owner)
            ->post(route('workout-templates.assign', $template), [
                'member_id' => $member->id,
            ])
            ->assertRedirect();

        $this->actingAs($owner)
            ->get(route('members.show', ['member' => $member, 'tab' => 'workout']))
            ->assertOk()
            ->assertSee('prime-rx--workout', false)
            ->assertSee('prime-rx-card__icon', false)
            ->assertSee('prime-rx-workout-grid', false)
            ->assertSee('Template Web Pernas')
            ->assertSee('Leg press')
            ->assertSee('Séries')
            ->assertSee('Reps')
            ->assertSee('90s');
    });

    it('creates a web diet prescription with inline meals foods and computed macros', function () {
        $owner = createOwner(['email' => 'rx.web.diet@test.app']);
        $member = createMemberFor($owner, [
            'name' => 'Aluno Dieta Web',
            'email' => 'aluno.dieta.web@test.app',
            'status' => 'active',
        ]);
        $rice = DietFood::query()->create([
            'parent_id' => $owner->id,
            'name' => 'Arroz integral',
            'food_group' => 'Carboidrato',
            'calories' => 120,
            'protein' => 2.6,
            'carbs' => 25,
            'fat' => 1,
        ]);
        $chicken = DietFood::query()->create([
            'parent_id' => $owner->id,
            'name' => 'Frango grelhado',
            'food_group' => 'Proteína',
            'calories' => 165,
            'protein' => 31,
            'carbs' => 0,
            'fat' => 3.6,
        ]);

        $this->actingAs($owner)
            ->post(route('members.diet.store', $member), [
                'title' => 'Dieta Web Cutting',
                'menu_name' => 'Cardápio Web Cutting',
                'scheduled_at' => now()->format('Y-m-d H:i:s'),
                'notes' => 'Ajustar água e sal conforme resposta.',
                'meals' => [
                    [
                        'name' => 'Almoço',
                        'time_label' => '12:30',
                        'foods' => [
                            [
                                'diet_food_id' => $rice->id,
                                'quantity_in_grams' => 150,
                            ],
                            [
                                'diet_food_id' => $chicken->id,
                                'quantity_in_grams' => 200,
                            ],
                        ],
                    ],
                ],
            ])
            ->assertRedirect(route('members.show', ['member' => $member, 'tab' => 'diet']));

        $prescription = DietPrescription::query()
            ->where('member_id', $member->id)
            ->where('title', 'Dieta Web Cutting')
            ->with('dietMenu.meals.mealFoods.dietFood')
            ->firstOrFail();

        expect($prescription->dietMenu)->not->toBeNull()
            ->and($prescription->dietMenu->name)->toBe('Cardápio Web Cutting')
            ->and((int) $prescription->dietMenu->meals_count)->toBe(1)
            ->and((float) $prescription->dietMenu->total_calories)->toBe(510.0)
            ->and($prescription->dietMenu->meals->first()->mealFoods)->toHaveCount(2);

        $this->actingAs($owner)
            ->get(route('members.show', ['member' => $member, 'tab' => 'diet']))
            ->assertOk()
            ->assertSee('prime-rx--diet', false)
            ->assertSee('data-diet-builder', false)
            ->assertSee('data-diet-macro-summary', false)
            ->assertSee('prime-rx-food-row', false)
            ->assertSee('data-diet-food-grams', false)
            ->assertSee('Dieta Web Cutting')
            ->assertSee('Arroz integral')
            ->assertSee('Frango grelhado')
            ->assertSee('Gerar PDF / Imprimir');

        $this->actingAs($owner)
            ->get(route('members.diet', $member))
            ->assertOk()
            ->assertSee('prime-rx--diet', false)
            ->assertSee('data-diet-builder', false)
            ->assertSee('Nova prescrição')
            ->assertSee('Arroz integral');
    });

    it('rejects web diet prescriptions using a menu from another tenant', function () {
        $ownerA = createOwner(['email' => 'rx.diet.a@test.app']);
        $ownerB = createOwner(['email' => 'rx.diet.b@test.app']);
        $memberA = createMemberFor($ownerA, ['email' => 'aluno.diet.a@test.app']);

        $otherTenantMenu = DietMenu::query()->create([
            'parent_id' => $ownerB->id,
            'name' => 'Cardápio privado B',
            'status' => 'published',
        ]);

        $this->actingAs($ownerA)
            ->post(route('members.diet.store', $memberA), [
                'title' => 'Cutting protegido',
                'diet_menu_id' => $otherTenantMenu->id,
                'scheduled_at' => now()->format('Y-m-d H:i:s'),
            ])
            ->assertSessionHasErrors('diet_menu_id');

        expect(DietPrescription::query()->where('member_id', $memberA->id)->count())->toBe(0);
    });

    it('rejects updating a web diet prescription to a menu from another tenant', function () {
        $ownerA = createOwner(['email' => 'rx.diet.update.a@test.app']);
        $ownerB = createOwner(['email' => 'rx.diet.update.b@test.app']);
        $memberA = createMemberFor($ownerA, ['email' => 'aluno.diet.update.a@test.app']);

        $ownedMenu = DietMenu::query()->create([
            'parent_id' => $ownerA->id,
            'name' => 'Cardápio A',
            'status' => 'published',
        ]);
        $otherTenantMenu = DietMenu::query()->create([
            'parent_id' => $ownerB->id,
            'name' => 'Cardápio B',
            'status' => 'published',
        ]);
        $prescription = DietPrescription::query()->create([
            'parent_id' => $ownerA->id,
            'member_id' => $memberA->id,
            'diet_menu_id' => $ownedMenu->id,
            'title' => 'Plano original',
            'status' => 'scheduled',
            'delivery_status' => 'PENDING',
            'scheduled_at' => now(),
        ]);

        $this->actingAs($ownerA)
            ->put(route('diet-prescriptions.update', $prescription), [
                'title' => 'Plano indevido',
                'diet_menu_id' => $otherTenantMenu->id,
                'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
            ])
            ->assertSessionHasErrors('diet_menu_id');

        expect($prescription->fresh()->diet_menu_id)->toBe($ownedMenu->id)
            ->and($prescription->fresh()->title)->toBe('Plano original');
    });
});
