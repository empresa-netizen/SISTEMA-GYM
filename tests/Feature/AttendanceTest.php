<?php

use App\Models\Attendance;
use App\Models\Member;
use App\Models\User;

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->owner->assignRole('owner');

    $this->member = Member::create([
        'name' => 'John Member',
        'email' => 'john@member.com',
        'parent_id' => $this->owner->id,
        'status' => 'active',
    ]);
});

test('owner can list attendances', function () {
    $attendance = Attendance::create([
        'member_id' => $this->member->id,
        'date' => now()->format('Y-m-d'),
        'check_in_time' => '09:00',
        'parent_id' => $this->owner->id,
    ]);

    $this->actingAs($this->owner)
        ->get(route('attendances.index'))
        ->assertOk()
        ->assertSee($this->member->name);
});

test('owner can check in member', function () {
    $this->actingAs($this->owner)
        ->post(route('attendances.store'), [
            'member_id' => $this->member->id,
            'date' => now()->format('Y-m-d'),
            'check_in_time' => '09:00',
            'notes' => 'Early bird',
        ])
        ->assertRedirect(route('attendances.index'));

    $this->assertDatabaseHas('attendances', [
        'member_id' => $this->member->id,
        'check_in_time' => '09:00:00', // Database usually stores seconds
        'parent_id' => $this->owner->id,
    ]);
});

test('owner cannot check in member twice on same day', function () {
    Attendance::create([
        'member_id' => $this->member->id,
        'date' => now()->format('Y-m-d'),
        'check_in_time' => '09:00',
        'parent_id' => $this->owner->id,
    ]);

    $this->actingAs($this->owner)
        ->post(route('attendances.store'), [
            'member_id' => $this->member->id,
            'date' => now()->format('Y-m-d'),
            'check_in_time' => '14:00',
        ])
        ->assertSessionHas('error');

    $this->assertCount(1, Attendance::all());
});

test('owner can check out member', function () {
    $attendance = Attendance::create([
        'member_id' => $this->member->id,
        'date' => now()->format('Y-m-d'),
        'check_in_time' => '09:00',
        'parent_id' => $this->owner->id,
    ]);

    $this->actingAs($this->owner)
        ->put(route('attendances.update', $attendance), [
            'check_out_time' => '10:30',
        ])
        ->assertRedirect(route('attendances.index'));

    $this->assertEquals('10:30', $attendance->fresh()->check_out_time->format('H:i'));
    $this->assertNotNull($attendance->fresh()->duration_minutes);
});

test('owner can view attendance report', function () {
    Attendance::create([
        'member_id' => $this->member->id,
        'date' => now()->format('Y-m-d'),
        'check_in_time' => '09:00',
        'check_out_time' => '10:00',
        'duration_minutes' => 60,
        'parent_id' => $this->owner->id,
    ]);

    $this->actingAs($this->owner)
        ->get(route('attendances.report', [
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]))
        ->assertOk()
        ->assertSee('Total de visitas')
        ->assertSee('60'); // Duration
});

test('owner cannot access attendance from other tenant', function () {
    $otherOwner = User::factory()->create();
    $otherMember = Member::create([
        'name' => 'Other Member',
        'email' => 'other@member.com',
        'parent_id' => $otherOwner->id,
        'status' => 'active',
    ]);
    $otherAttendance = Attendance::create([
        'member_id' => $otherMember->id,
        'date' => now()->format('Y-m-d'),
        'check_in_time' => '09:00:00',
        'parent_id' => $otherOwner->id,
    ]);

    $this->actingAs($this->owner)
        ->put(route('attendances.update', $otherAttendance), [
            'check_out_time' => '10:00',
        ])
        ->assertNotFound();

    $this->actingAs($this->owner)
        ->delete(route('attendances.destroy', $otherAttendance))
        ->assertNotFound();
});
