<?php

use App\Models\Category;
use App\Models\GymClass;
use App\Models\Trainer;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->owner->assignRole('owner');

    $this->category = Category::create([
        'name' => 'Cardio',
        'color' => '#FF0000',
        'parent_id' => $this->owner->id,
    ]);

    $this->trainer = Trainer::create([
        'name' => 'John Trainer',
        'email' => 'john@trainer.com',
        'parent_id' => $this->owner->id,
        'status' => 'active',
    ]);
});

test('owner can list classes', function () {
    $class = GymClass::create([
        'name' => 'Morning Cardio',
        'category_id' => $this->category->id,
        'max_capacity' => 20,
        'duration_minutes' => 60,
        'difficulty_level' => 'beginner',
        'status' => 'active',
        'parent_id' => $this->owner->id,
    ]);

    $this->actingAs($this->owner)
        ->get(route('gym-classes.index'))
        ->assertOk()
        // Gym classes index is rendered via DataTables (AJAX), so the initial HTML
        // may not contain row data.
        ->assertSee('Aulas');
});

test('owner can create class with schedules', function () {
    Storage::fake('public');
    // Avoid GD dependency in test container.
    $image = UploadedFile::fake()->create('class.jpg', 10, 'image/jpeg');

    $this->actingAs($this->owner)
        ->post(route('gym-classes.store'), [
            'name' => 'Evening Yoga',
            'category_id' => $this->category->id,
            'max_capacity' => 15,
            'duration_minutes' => 45,
            'difficulty_level' => 'intermediate',
            'status' => 'active',
            'image' => $image,
            'schedules' => [
                [
                    'day_of_week' => 'monday',
                    'start_time' => '18:00',
                    'end_time' => '18:45',
                    'trainer_id' => $this->trainer->id,
                    'room_location' => 'Room A',
                ],
            ],
        ])
        ->assertRedirect(route('gym-classes.index'));

    $this->assertDatabaseHas('gym_classes', [
        'name' => 'Evening Yoga',
        'parent_id' => $this->owner->id,
    ]);

    $class = GymClass::where('name', 'Evening Yoga')->first();
    $this->assertNotNull($class->class_id);
    $this->assertCount(1, $class->schedules);
    $this->assertEquals('monday', $class->schedules->first()->day_of_week);
    Storage::disk('public')->assertExists($class->image);
});

test('owner can update class', function () {
    $class = GymClass::create([
        'name' => 'Morning Cardio',
        'category_id' => $this->category->id,
        'max_capacity' => 20,
        'duration_minutes' => 60,
        'difficulty_level' => 'beginner',
        'status' => 'active',
        'parent_id' => $this->owner->id,
    ]);

    $this->actingAs($this->owner)
        ->put(route('gym-classes.update', $class), [
            'name' => 'Morning HIIT',
            'category_id' => $this->category->id,
            'max_capacity' => 25,
            'duration_minutes' => 45,
            'difficulty_level' => 'advanced',
            'status' => 'active',
        ])
        ->assertRedirect(route('gym-classes.index'));

    $this->assertEquals('Morning HIIT', $class->fresh()->name);
    $this->assertEquals(25, $class->fresh()->max_capacity);
});

test('owner can delete class', function () {
    $class = GymClass::create([
        'name' => 'Morning Cardio',
        'category_id' => $this->category->id,
        'max_capacity' => 20,
        'duration_minutes' => 60,
        'difficulty_level' => 'beginner',
        'status' => 'active',
        'parent_id' => $this->owner->id,
    ]);

    $this->actingAs($this->owner)
        ->delete(route('gym-classes.destroy', $class))
        ->assertOk()
        ->assertJson(['status' => true]);

    $this->assertModelMissing($class);
});

test('owner cannot access classes from other tenant', function () {
    $otherOwner = User::factory()->create();
    $otherCategory = Category::create([
        'name' => 'Other Cat',
        'color' => '#000000',
        'parent_id' => $otherOwner->id,
    ]);
    $otherClass = GymClass::create([
        'name' => 'Other Class',
        'category_id' => $otherCategory->id,
        'max_capacity' => 10,
        'duration_minutes' => 30,
        'difficulty_level' => 'beginner',
        'status' => 'active',
        'parent_id' => $otherOwner->id,
    ]);

    $this->actingAs($this->owner)
        ->get(route('gym-classes.show', $otherClass))
        ->assertNotFound();

    $this->actingAs($this->owner)
        ->put(route('gym-classes.update', $otherClass), [
            'name' => 'Hacked',
        ])
        ->assertNotFound();

    $this->actingAs($this->owner)
        ->delete(route('gym-classes.destroy', $otherClass))
        ->assertNotFound();
});
