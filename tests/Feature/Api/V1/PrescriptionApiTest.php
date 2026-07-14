<?php

use App\Models\DietMenu;
use App\Models\Workout;
use App\Models\WorkoutActivity;
use Laravel\Sanctum\Sanctum;

describe('API V1 prescriptions contract', function () {
    it('creates diet prescriptions with a tenant scoped diet menu', function () {
        $owner = createOwner(['email' => 'v1.diet.prescription.owner@test.app']);
        $member = createMemberFor($owner, ['email' => 'ana.dieta.v1@test.app']);
        $menu = DietMenu::query()->create([
            'parent_id' => $owner->id,
            'name' => 'Cutting V1',
            'status' => 'published',
            'meals_count' => 0,
            'total_calories' => 0,
        ]);

        Sanctum::actingAs($owner);

        $this->postJson('/api/v1/prescriptions/diet', [
            'member_id' => $member->id,
            'diet_menu_id' => $menu->id,
            'title' => 'Dieta Cutting V1',
            'notes' => 'Ajustar água e sono.',
            'status' => 'sent',
            'delivery_status' => 'DELIVERED',
            'scheduled_at' => now()->toISOString(),
        ])
            ->assertCreated()
            ->assertJsonPath('message', 'Prescricao alimentar criada com sucesso.')
            ->assertJsonPath('data.member_id', $member->id)
            ->assertJsonPath('data.diet_menu_id', $menu->id)
            ->assertJsonPath('data.diet_menu.name', 'Cutting V1')
            ->assertJsonPath('data.delivery_status', 'DELIVERED');
    });

    it('does not create diet prescriptions with menus from another tenant', function () {
        $owner = createOwner(['email' => 'v1.diet.prescription.owner-a@test.app']);
        $otherOwner = createOwner(['email' => 'v1.diet.prescription.owner-b@test.app']);
        $member = createMemberFor($owner, ['email' => 'ana.dieta.local@test.app']);
        $otherMenu = DietMenu::query()->create([
            'parent_id' => $otherOwner->id,
            'name' => 'Menu de outro tenant',
            'status' => 'published',
            'meals_count' => 0,
            'total_calories' => 0,
        ]);

        Sanctum::actingAs($owner);

        $this->postJson('/api/v1/prescriptions/diet', [
            'member_id' => $member->id,
            'diet_menu_id' => $otherMenu->id,
            'title' => 'Dieta vazada',
        ])->assertNotFound();
    });

    it('creates workout prescriptions with ordered activities', function () {
        $owner = createOwner(['email' => 'v1.prescription.owner@test.app']);
        $member = createMemberFor($owner, [
            'name' => 'Ana Treino V1',
            'email' => 'ana.treino.v1@test.app',
        ]);

        Sanctum::actingAs($owner);

        $this->postJson('/api/v1/prescriptions/workout', [
            'member_id' => $member->id,
            'name' => 'Treino A — Inferiores',
            'description' => 'Força e hipertrofia',
            'workout_date' => now()->addDay()->toDateString(),
            'notes' => 'Subir carga se a técnica estiver limpa.',
            'activities' => [
                [
                    'exercise_name' => 'Agachamento livre',
                    'sets' => 4,
                    'reps' => 8,
                    'weight_kg' => 60,
                    'rest_seconds' => 120,
                    'order' => 1,
                    'notes' => 'Cadência controlada.',
                ],
                [
                    'exercise_name' => 'Mesa flexora',
                    'sets' => 3,
                    'reps' => 12,
                    'weight_kg' => 35.5,
                    'order' => 2,
                ],
            ],
        ])
            ->assertCreated()
            ->assertJsonPath('message', 'Prescricao de treino criada com sucesso.')
            ->assertJsonPath('data.member_id', $member->id)
            ->assertJsonPath('data.name', 'Treino A — Inferiores')
            ->assertJsonPath('data.activities_total', 2)
            ->assertJsonPath('data.activities.0.exercise_name', 'Agachamento livre')
            ->assertJsonPath('data.activities.0.details', '4 sets × 8 reps | 60.00 kg')
            ->assertJsonPath('data.activities.1.exercise_name', 'Mesa flexora')
            ->assertJsonPath('data.activities.1.weight_kg', 35.5);

        $workout = Workout::query()
            ->where('parent_id', $owner->id)
            ->where('member_id', $member->id)
            ->first();

        expect($workout)->not->toBeNull()
            ->and($workout->workout_id)->toStartWith('#WRK-')
            ->and(WorkoutActivity::query()
                ->where('workout_id', $workout->id)
                ->orderBy('order')
                ->pluck('exercise_name')
                ->all())->toBe(['Agachamento livre', 'Mesa flexora']);
    });

    it('does not create workout prescriptions for members from another tenant', function () {
        $owner = createOwner(['email' => 'v1.prescription.owner-a@test.app']);
        $otherOwner = createOwner(['email' => 'v1.prescription.owner-b@test.app']);
        $otherMember = createMemberFor($otherOwner, ['email' => 'aluno.outro.tenant@test.app']);

        Sanctum::actingAs($owner);

        $this->postJson('/api/v1/prescriptions/workout', [
            'member_id' => $otherMember->id,
            'name' => 'Treino vazado',
            'activities' => [
                ['exercise_name' => 'Supino reto'],
            ],
        ])->assertNotFound();

        expect(Workout::query()
            ->where('parent_id', $owner->id)
            ->where('name', 'Treino vazado')
            ->exists())->toBeFalse();
    });

    it('validates workout prescription payloads', function () {
        $owner = createOwner(['email' => 'v1.prescription.validation@test.app']);

        Sanctum::actingAs($owner);

        $this->postJson('/api/v1/prescriptions/workout', [
            'member_id' => null,
            'name' => '',
            'activities' => [],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['member_id', 'name', 'activities']);
    });
});
