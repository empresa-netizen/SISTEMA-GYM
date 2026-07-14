<?php

use App\Models\Member;
use App\Models\Trainer;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutActivity;

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->owner->assignRole('owner');

    $this->member = Member::create([
        'name' => 'John Member',
        'email' => 'john@member.com',
        'parent_id' => $this->owner->id,
        'status' => 'active',
    ]);

    $this->trainer = Trainer::create([
        'name' => 'John Trainer',
        'email' => 'john@trainer.com',
        'parent_id' => $this->owner->id,
        'status' => 'active',
    ]);
});

test('owner can list workouts', function () {
    $workout = Workout::create([
        'name' => 'Full Body',
        'member_id' => $this->member->id,
        'trainer_id' => $this->trainer->id,
        'status' => 'active',
        'parent_id' => $this->owner->id,
    ]);

    $this->actingAs($this->owner)
        ->get(route('workouts.index'))
        ->assertOk()
        ->assertSee('Prescrições de treino')
        ->assertSee('Full Body');
});

test('owner can create workout with activities', function () {
    $this->actingAs($this->owner)
        ->post(route('workouts.store'), [
            'name' => 'Leg Day',
            'member_id' => $this->member->id,
            'trainer_id' => $this->trainer->id,
            'status' => 'active',
            'workout_date' => now()->format('Y-m-d'),
            'activities' => [
                [
                    'exercise_name' => 'Squats',
                    'sets' => 3,
                    'reps' => 10,
                    'weight_kg' => 100,
                ],
            ],
        ])
        ->assertRedirect(route('members.show', ['member' => $this->member, 'tab' => 'workout']));

    $this->assertDatabaseHas('workouts', [
        'name' => 'Leg Day',
        'parent_id' => $this->owner->id,
    ]);

    $workout = Workout::where('name', 'Leg Day')->first();
    $this->assertNotNull($workout->workout_id);
    $this->assertCount(1, $workout->activities);
    $this->assertEquals('Squats', $workout->activities->first()->exercise_name);
});

test('owner can update workout', function () {
    $workout = Workout::create([
        'name' => 'Full Body',
        'member_id' => $this->member->id,
        'status' => 'active',
        'parent_id' => $this->owner->id,
    ]);

    $this->actingAs($this->owner)
        ->put(route('workouts.update', $workout), [
            'name' => 'Upper Body',
            'status' => 'completed',
        ])
        ->assertRedirect(route('members.show', ['member' => $this->member, 'tab' => 'workout']));

    $this->assertEquals('Upper Body', $workout->fresh()->name);
    $this->assertEquals('completed', $workout->fresh()->status);
});

test('owner can update workout activities from prescription editor', function () {
    $workout = Workout::create([
        'name' => 'Full Body',
        'member_id' => $this->member->id,
        'status' => 'active',
        'parent_id' => $this->owner->id,
    ]);
    $workout->activities()->create([
        'exercise_name' => 'Old Squat',
        'sets' => 2,
        'reps' => 12,
        'order' => 0,
    ]);

    $this->actingAs($this->owner)
        ->put(route('workouts.update', $workout), [
            'name' => 'Upper Body',
            'member_id' => $this->member->id,
            'trainer_id' => $this->trainer->id,
            'status' => 'active',
            'sync_activities' => '1',
            'activities' => [
                [
                    'exercise_name' => 'Bench Press',
                    'sets' => 4,
                    'reps' => 8,
                    'weight_kg' => 80,
                    'rest_seconds' => 90,
                ],
                [
                    'exercise_name' => 'Pull Down',
                    'sets' => 3,
                    'reps' => 10,
                    'weight_kg' => 55,
                    'rest_seconds' => 75,
                ],
            ],
        ])
        ->assertRedirect(route('members.show', ['member' => $this->member, 'tab' => 'workout']));

    $activities = WorkoutActivity::query()
        ->where('workout_id', $workout->id)
        ->orderBy('order')
        ->get();

    expect($activities)->toHaveCount(2)
        ->and($activities->pluck('exercise_name')->all())->toBe(['Bench Press', 'Pull Down'])
        ->and((int) $activities->first()->sets)->toBe(4)
        ->and((float) $activities->first()->weight_kg)->toBe(80.0);

    $this->assertDatabaseMissing('workout_activities', [
        'workout_id' => $workout->id,
        'exercise_name' => 'Old Squat',
    ]);
});

test('owner cannot create workout assigned to member or trainer from other tenant', function () {
    $otherOwner = User::factory()->create();
    $otherMember = Member::create([
        'name' => 'Other Member',
        'email' => 'other.member@test.app',
        'parent_id' => $otherOwner->id,
        'status' => 'active',
    ]);
    $otherTrainer = Trainer::create([
        'name' => 'Other Trainer',
        'email' => 'other.trainer@test.app',
        'parent_id' => $otherOwner->id,
        'status' => 'active',
    ]);

    $this->actingAs($this->owner)
        ->post(route('workouts.store'), [
            'name' => 'Cross Tenant Workout',
            'member_id' => $otherMember->id,
            'trainer_id' => $otherTrainer->id,
            'status' => 'active',
            'workout_date' => now()->format('Y-m-d'),
        ])
        ->assertSessionHasErrors(['member_id', 'trainer_id']);

    $this->assertDatabaseMissing('workouts', [
        'name' => 'Cross Tenant Workout',
        'parent_id' => $this->owner->id,
    ]);
});

test('owner cannot update workout to member or trainer from other tenant', function () {
    $otherOwner = User::factory()->create();
    $otherMember = Member::create([
        'name' => 'Other Member',
        'email' => 'other.update.member@test.app',
        'parent_id' => $otherOwner->id,
        'status' => 'active',
    ]);
    $otherTrainer = Trainer::create([
        'name' => 'Other Trainer',
        'email' => 'other.update.trainer@test.app',
        'parent_id' => $otherOwner->id,
        'status' => 'active',
    ]);
    $workout = Workout::create([
        'name' => 'Full Body',
        'member_id' => $this->member->id,
        'trainer_id' => $this->trainer->id,
        'status' => 'active',
        'parent_id' => $this->owner->id,
    ]);

    $this->actingAs($this->owner)
        ->put(route('workouts.update', $workout), [
            'name' => 'Cross Tenant Update',
            'member_id' => $otherMember->id,
            'trainer_id' => $otherTrainer->id,
            'status' => 'active',
        ])
        ->assertSessionHasErrors(['member_id', 'trainer_id']);

    expect($workout->fresh()->member_id)->toBe($this->member->id)
        ->and($workout->fresh()->trainer_id)->toBe($this->trainer->id)
        ->and($workout->fresh()->name)->toBe('Full Body');
});

test('owner can delete workout', function () {
    $workout = Workout::create([
        'name' => 'Full Body',
        'member_id' => $this->member->id,
        'status' => 'active',
        'parent_id' => $this->owner->id,
    ]);

    $this->actingAs($this->owner)
        ->delete(route('workouts.destroy', $workout))
        ->assertOk()
        ->assertJson(['status' => true]);

    $this->assertModelMissing($workout);
});

test('owner can view todays workouts', function () {
    $workout = Workout::create([
        'name' => 'Today Workout',
        'member_id' => $this->member->id,
        'status' => 'active',
        'workout_date' => now()->format('Y-m-d'),
        'parent_id' => $this->owner->id,
    ]);

    // Create past workout
    Workout::create([
        'name' => 'Past Workout',
        'member_id' => $this->member->id,
        'status' => 'active',
        'workout_date' => now()->subDay()->format('Y-m-d'),
        'parent_id' => $this->owner->id,
    ]);

    $this->actingAs($this->owner)
        ->get(route('workouts.today'))
        ->assertOk()
        ->assertSee('Today Workout')
        ->assertDontSee('Past Workout');
});

test('owner cannot access workouts from other tenant', function () {
    $otherOwner = User::factory()->create();
    $otherWorkout = Workout::create([
        'name' => 'Other Workout',
        'status' => 'active',
        'parent_id' => $otherOwner->id,
    ]);

    $this->actingAs($this->owner)
        ->get(route('workouts.show', $otherWorkout))
        ->assertNotFound();

    $this->actingAs($this->owner)
        ->put(route('workouts.update', $otherWorkout), [
            'name' => 'Hacked',
        ])
        ->assertNotFound();

    $this->actingAs($this->owner)
        ->delete(route('workouts.destroy', $otherWorkout))
        ->assertNotFound();
});
