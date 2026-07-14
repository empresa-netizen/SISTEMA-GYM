<?php

use App\Models\Conversation;
use App\Models\DietFood;
use App\Models\DietMenu;
use App\Models\DietPrescription;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutActivity;
use App\Models\WorkoutActivityLog;
use App\Notifications\InAppAlert;
use App\Services\ChatMessenger;
use App\Services\DietMacroAuditor;
use App\Services\WorkoutSessionLogger;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

describe('E2E journey loops', function () {
    it('logs workout activity check-off and completes the session', function () {
        $owner = createOwner(['email' => 'e2e.coach@test.app']);
        $member = createMemberFor($owner, [
            'name' => 'Ana Beatriz',
            'email' => 'anabeatriz.e2e@test.app',
        ]);

        $workout = Workout::query()->create([
            'parent_id' => $owner->id,
            'member_id' => $member->id,
            'name' => 'Treino A — Hipertrofia',
            'status' => 'active',
            'scheduled_date' => now()->toDateString(),
        ]);

        $activity = WorkoutActivity::query()->create([
            'workout_id' => $workout->id,
            'exercise_name' => 'Agachamento',
            'sets' => 4,
            'reps' => 10,
            'weight_kg' => 40,
            'order' => 1,
            'is_completed' => false,
        ]);

        $clientToken = $owner->createToken('client-'.$member->id, ['client:'.$member->id])->plainTextToken;

        $this->withToken($clientToken)
            ->postJson("/api/workouts/{$workout->id}/activities/{$activity->id}/log", [
                'is_completed' => true,
                'sets' => 4,
                'reps' => 8,
                'weight_kg' => 45,
                'notes' => 'Última série pesada',
            ])
            ->assertCreated()
            ->assertJsonPath('message', 'Exercicio registrado.');

        expect($activity->fresh()->is_completed)->toBeTrue()
            ->and((float) $activity->fresh()->weight_kg)->toBe(45.0)
            ->and(WorkoutActivityLog::query()->where('workout_activity_id', $activity->id)->count())->toBe(1);

        $this->withToken($clientToken)
            ->postJson("/api/workouts/{$workout->id}/complete", [
                'comment' => 'Sessão fechada no app',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');

        expect($workout->fresh()->status)->toBe('completed');
    });

    it('audits diet macros from meal portions and renders print view', function () {
        $owner = createOwner(['email' => 'e2e.diet@test.app']);
        $member = createMemberFor($owner, [
            'name' => 'Ana Beatriz',
            'email' => 'ana.diet@test.app',
        ]);

        $menu = DietMenu::query()->create([
            'parent_id' => $owner->id,
            'name' => 'Fase 1',
            'status' => 'published',
            'meals_count' => 0,
            'total_calories' => 0,
            'description' => 'Cutting leve',
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

        $almoco = $menu->meals()->create([
            'name' => 'Almoço',
            'time_label' => '12:00',
            'order' => 0,
        ]);
        $almoco->mealFoods()->create([
            'diet_food_id' => $frango->id,
            'quantity_in_grams' => 150, // 1.5 × 100g
            'order' => 0,
        ]);
        $almoco->mealFoods()->create([
            'diet_food_id' => $arroz->id,
            'quantity_in_grams' => 100,
            'order' => 1,
        ]);

        $menu->syncAggregateCounters();

        $prescription = DietPrescription::query()->create([
            'parent_id' => $owner->id,
            'member_id' => $member->id,
            'diet_menu_id' => $menu->id,
            'title' => 'Dieta Fase 1',
            'notes' => 'Beber 3L de água',
            'status' => 'sent',
            'delivery_status' => 'DELIVERED',
            'scheduled_at' => now(),
            'sent_at' => now(),
        ]);

        $summary = app(DietMacroAuditor::class)->summarize($prescription);

        // Frango 150g: 247.5 kcal / 46.5p / 0c / 5.4f
        // Arroz 100g: 130 / 2.7 / 28 / 0.3
        expect($summary['prescribed']['calories'])->toBe(377.5)
            ->and($summary['prescribed']['protein'])->toBe(49.2)
            ->and($summary['prescribed']['carbs'])->toBe(28.0)
            ->and(round($summary['prescribed']['fat'], 1))->toBe(5.7)
            ->and($summary['menu_kcal'])->toBe(377.5)
            ->and($summary['meals_count'])->toBe(1)
            ->and($summary['meals'])->toHaveCount(1)
            ->and($summary['catalog']['calories'])->toBe(295.0);

        $this->actingAs($owner)
            ->get(route('diet-prescriptions.print', $prescription))
            ->assertOk()
            ->assertSee('Dieta Fase 1')
            ->assertSee('Almoço')
            ->assertSee('Frango grelhado')
            ->assertSee('Imprimir / PDF');
    });
    it('exchanges chat messages and marks coach messages as read', function () {
        Notification::fake();

        $owner = createOwner(['email' => 'e2e.chat@test.app']);
        $memberUser = User::factory()->create([
            'email' => 'ana.chat.user@test.app',
            'password' => Hash::make('password'),
            'parent_id' => $owner->id,
        ]);
        $member = createMemberFor($owner, [
            'name' => 'Ana Beatriz',
            'email' => 'ana.chat@test.app',
            'user_id' => $memberUser->id,
        ]);

        $conversation = Conversation::query()->create([
            'parent_id' => $owner->id,
            'member_id' => $member->id,
            'last_message_at' => now(),
            'unread_by_coach' => false,
        ]);

        $coachMessage = app(ChatMessenger::class)->sendFromCoach($conversation, 'Oi Ana, treino ok?');

        Notification::assertSentTo($memberUser, InAppAlert::class);

        $clientToken = $owner->createToken('client-'.$member->id, ['client:'.$member->id])->plainTextToken;

        $this->withToken($clientToken)
            ->postJson('/api/messages/conversation/read')
            ->assertOk()
            ->assertJsonPath('updated', 1);

        expect($coachMessage->fresh()->read_at)->not->toBeNull();

        $this->withToken($clientToken)
            ->postJson('/api/messages/conversation', [
                'content' => 'Sim coach, fechei o treino A!',
            ])
            ->assertCreated();

        expect($conversation->fresh()->unread_by_coach)->toBeTrue()
            ->and($conversation->fresh()->last_message)->toContain('fechei o treino');

        Notification::assertSentTo($owner, InAppAlert::class);
    });

    it('auto-completes workout when all activities are checked', function () {
        $owner = createOwner(['email' => 'e2e.auto@test.app']);
        $member = createMemberFor($owner, ['email' => 'auto.member@test.app']);

        $workout = Workout::query()->create([
            'parent_id' => $owner->id,
            'member_id' => $member->id,
            'name' => 'Treino curto',
            'status' => 'active',
        ]);

        $a1 = WorkoutActivity::query()->create([
            'workout_id' => $workout->id,
            'exercise_name' => 'Flexão',
            'sets' => 3,
            'reps' => 12,
            'order' => 1,
            'is_completed' => false,
        ]);
        $a2 = WorkoutActivity::query()->create([
            'workout_id' => $workout->id,
            'exercise_name' => 'Prancha',
            'sets' => 3,
            'reps' => 1,
            'order' => 2,
            'is_completed' => false,
        ]);

        $logger = app(WorkoutSessionLogger::class);
        $logger->logActivity($workout, $a1, ['is_completed' => true]);
        expect($workout->fresh()->status)->toBe('active');

        $logger->logActivity($workout, $a2, ['is_completed' => true]);
        expect($workout->fresh()->status)->toBe('completed');
    });
});
