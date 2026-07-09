<?php

use App\Models\Expense;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->owner->assignRole('owner');

    $this->type = Type::create([
        'name' => 'Utilities',
        'type' => 'expense',
        'parent_id' => $this->owner->id,
        'status' => 'active',
    ]);
});

test('owner can list expenses', function () {
    $expense = Expense::create([
        'type_id' => $this->type->id,
        'title' => 'Electricity Bill',
        'amount' => 100,
        'expense_date' => now()->format('Y-m-d'),
        'payment_method' => 'bank_transfer',
        'parent_id' => $this->owner->id,
    ]);

    $this->actingAs($this->owner)
        ->get(route('expenses.index'))
        ->assertOk()
        // Expenses index is rendered via DataTables (AJAX), so the initial HTML
        // may not contain row data.
        ->assertSee('Despesas');
});

test('owner can create expense with receipt', function () {
    Storage::fake('public');
    // Avoid GD dependency in test container.
    $receipt = UploadedFile::fake()->create('receipt.jpg', 10, 'image/jpeg');

    $this->actingAs($this->owner)
        ->post(route('expenses.store'), [
            'type_id' => $this->type->id,
            'title' => 'Water Bill',
            'amount' => 50,
            'expense_date' => now()->format('Y-m-d'),
            'payment_method' => 'cash',
            'receipt' => $receipt,
        ])
        ->assertRedirect(route('expenses.index'));

    $this->assertDatabaseHas('expenses', [
        'title' => 'Water Bill',
        'parent_id' => $this->owner->id,
    ]);

    $expense = Expense::where('title', 'Water Bill')->first();
    $this->assertNotNull($expense->expense_number);
    Storage::disk('public')->assertExists($expense->receipt);
});

test('owner can update expense', function () {
    $expense = Expense::create([
        'type_id' => $this->type->id,
        'title' => 'Electricity Bill',
        'amount' => 100,
        'expense_date' => now()->format('Y-m-d'),
        'payment_method' => 'bank_transfer',
        'parent_id' => $this->owner->id,
    ]);

    $this->actingAs($this->owner)
        ->put(route('expenses.update', $expense), [
            'type_id' => $this->type->id,
            'title' => 'Updated Bill',
            'amount' => 120,
            'expense_date' => now()->format('Y-m-d'),
            'payment_method' => 'bank_transfer',
        ])
        ->assertRedirect(route('expenses.index'));

    $this->assertEquals('Updated Bill', $expense->fresh()->title);
    $this->assertEquals(120, $expense->fresh()->amount);
});

test('owner can delete expense', function () {
    $expense = Expense::create([
        'type_id' => $this->type->id,
        'title' => 'Electricity Bill',
        'amount' => 100,
        'expense_date' => now()->format('Y-m-d'),
        'payment_method' => 'bank_transfer',
        'parent_id' => $this->owner->id,
    ]);

    $this->actingAs($this->owner)
        ->post(route('expenses.destroy', $expense))
        ->assertOk()
        ->assertJson(['status' => true]);

    $this->assertModelMissing($expense);
});

test('owner cannot access expense from other tenant', function () {
    $otherOwner = User::factory()->create();
    $otherType = Type::create([
        'name' => 'Other Type',
        'type' => 'expense',
        'parent_id' => $otherOwner->id,
    ]);
    $otherExpense = Expense::create([
        'type_id' => $otherType->id,
        'title' => 'Other Expense',
        'amount' => 50,
        'expense_date' => now()->format('Y-m-d'),
        'payment_method' => 'cash',
        'parent_id' => $otherOwner->id,
    ]);

    $this->actingAs($this->owner)
        ->get(route('expenses.show', $otherExpense))
        ->assertNotFound();

    $this->actingAs($this->owner)
        ->put(route('expenses.update', $otherExpense), [
            'title' => 'Hacked',
        ])
        ->assertNotFound();

    $this->actingAs($this->owner)
        ->post(route('expenses.destroy', $otherExpense))
        ->assertNotFound();
});
