<?php

use App\Models\Member;
use App\Models\Trainer;
use App\Models\User;
use App\Models\Workout;

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
        ->assertRedirect(route('workouts.index'));

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
        ->assertRedirect(route('workouts.index'));

    $this->assertEquals('Upper Body', $workout->fresh()->name);
    $this->assertEquals('completed', $workout->fresh()->status);
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
