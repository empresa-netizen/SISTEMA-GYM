<?php

use Laravel\Sanctum\Sanctum;

describe('API V1 Members contract', function () {
    it('lists members with MemberResource fields and pagination meta', function () {
        $owner = createOwner();
        createMemberFor($owner, ['name' => 'Ana Beatriz', 'email' => 'ana@test.app', 'status' => 'active']);
        createMemberFor($owner, ['name' => 'Carlos Lima', 'email' => 'carlos@test.app', 'status' => 'active']);

        Sanctum::actingAs($owner);

        $this->getJson('/api/v1/members?per_page=15')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'member_code',
                        'parent_id',
                        'user_id',
                        'coach_user_id',
                        'name',
                        'email',
                        'phone',
                        'date_of_birth',
                        'gender',
                        'address',
                        'photo',
                        'status',
                        'membership_plan_id',
                        'membership_start_date',
                        'membership_end_date',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => ['current_page', 'from', 'last_page', 'per_page', 'to', 'total'],
            ])
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.parent_id', $owner->id);
    });

    it('shows a single member with MemberResource contract', function () {
        $owner = createOwner();
        $member = createMemberFor($owner, ['name' => 'Ana Beatriz', 'email' => 'ana.show@test.app']);

        Sanctum::actingAs($owner);

        $this->getJson('/api/v1/members/'.$member->id)
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'member_code',
                    'name',
                    'email',
                    'status',
                    'parent_id',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.id', $member->id)
            ->assertJsonPath('data.name', 'Ana Beatriz')
            ->assertJsonPath('data.status', 'active');
    });

    it('does not leak members from another tenant', function () {
        $ownerA = createOwner(['email' => 'a@test.app']);
        $ownerB = createOwner(['email' => 'b@test.app']);
        createMemberFor($ownerB, ['name' => 'Outro Tenant', 'email' => 'outro@test.app']);

        Sanctum::actingAs($ownerA);

        $this->getJson('/api/v1/members')
            ->assertOk()
            ->assertJsonPath('meta.total', 0);
    });
});
