<?php

use App\Models\Invoice;
use App\Models\Member;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function () {
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
    })
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Helpers — API V1 / multi-tenant
|--------------------------------------------------------------------------
*/

function createOwner(array $attributes = []): User
{
    $user = User::factory()->create(array_merge([
        'password' => Hash::make('password'),
    ], $attributes));

    $user->assignRole('owner');

    return $user->fresh();
}

function createMemberFor(User $owner, array $attributes = []): Member
{
    return Member::query()->create(array_merge([
        'parent_id' => $owner->id,
        'name' => 'Aluno Teste',
        'email' => 'aluno.'.uniqid().'@example.com',
        'phone' => '11999990000',
        'status' => 'active',
        'gender' => 'female',
    ], $attributes));
}

function createInvoiceFor(User $owner, Member $member, array $attributes = []): Invoice
{
    return Invoice::query()->create(array_merge([
        'parent_id' => $owner->id,
        'member_id' => $member->id,
        'invoice_date' => now()->toDateString(),
        'due_date' => now()->addDays(7)->toDateString(),
        'subtotal' => 100,
        'tax_amount' => 0,
        'discount_amount' => 0,
        'total_amount' => 100,
        'paid_amount' => 0,
        'status' => 'unpaid',
        'notes' => null,
    ], $attributes));
}

function asOwner(?User $owner = null)
{
    $owner ??= createOwner();

    return test()->actingAs($owner);
}
