<?php

use App\Models\Invoice;
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

test('owner can list invoices', function () {
    $invoice = Invoice::create([
        'member_id' => $this->member->id,
        'invoice_date' => now()->format('Y-m-d'),
        'due_date' => now()->addDays(7)->format('Y-m-d'),
        'status' => 'unpaid',
        'total_amount' => 100,
        'parent_id' => $this->owner->id,
    ]);

    $this->actingAs($this->owner)
        ->get(route('invoices.index'))
        ->assertOk()
        // Invoices index is rendered via DataTables (AJAX), so the initial HTML
        // may not contain row data.
        ->assertSee('Vendas e faturas');
});

test('owner can create invoice with items', function () {
    $response = $this->actingAs($this->owner)
        ->post(route('invoices.store'), [
            'member_id' => $this->member->id,
            'invoice_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'items' => [
                [
                    'description' => 'Membership Fee',
                    'quantity' => 1,
                    'unit_price' => 100,
                ],
            ],
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('invoices.index'));

    $this->assertDatabaseHas('invoices', [
        'member_id' => $this->member->id,
        'total_amount' => 100, // 1 * 100
        'parent_id' => $this->owner->id,
    ]);

    $invoice = Invoice::where('member_id', $this->member->id)->first();
    $this->assertNotNull($invoice->invoice_number);
    $this->assertCount(1, $invoice->items);
});

test('owner can add payment to invoice', function () {
    $invoice = Invoice::create([
        'member_id' => $this->member->id,
        'invoice_date' => now()->format('Y-m-d'),
        'due_date' => now()->addDays(7)->format('Y-m-d'),
        'status' => 'unpaid',
        'total_amount' => 100,
        'paid_amount' => 0,
        'parent_id' => $this->owner->id,
    ]);

    $this->actingAs($this->owner)
        ->post(route('invoices.addPayment', $invoice), [
            'amount' => 50,
            'payment_date' => now()->format('Y-m-d'),
            'payment_method' => 'cash',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $this->assertEquals(50, $invoice->fresh()->paid_amount);
    $this->assertEquals('partially_paid', $invoice->fresh()->status);

    // Pay remaining
    $this->actingAs($this->owner)
        ->post(route('invoices.addPayment', $invoice), [
            'amount' => 50,
            'payment_date' => now()->format('Y-m-d'),
            'payment_method' => 'cash',
        ])
        ->assertRedirect();

    $this->assertEquals(100, $invoice->fresh()->paid_amount);
    $this->assertEquals('paid', $invoice->fresh()->status);
});

test('owner cannot edit paid invoice', function () {
    $invoice = Invoice::create([
        'member_id' => $this->member->id,
        'invoice_date' => now()->format('Y-m-d'),
        'due_date' => now()->addDays(7)->format('Y-m-d'),
        'status' => 'paid',
        'total_amount' => 100,
        'paid_amount' => 100,
        'parent_id' => $this->owner->id,
    ]);

    $this->actingAs($this->owner)
        ->get(route('invoices.edit', $invoice))
        ->assertSessionHas('error');

    $this->actingAs($this->owner)
        ->put(route('invoices.update', $invoice), [
            'notes' => 'Updated',
        ])
        ->assertSessionHas('error');
});

test('owner cannot access invoice from other tenant', function () {
    $otherOwner = User::factory()->create();
    $otherInvoice = Invoice::create([
        'member_id' => $this->member->id, // Member doesn't matter for this check usually, but parent_id does
        'invoice_date' => now()->format('Y-m-d'),
        'due_date' => now()->addDays(7)->format('Y-m-d'),
        'status' => 'unpaid',
        'parent_id' => $otherOwner->id,
    ]);

    $this->actingAs($this->owner)
        ->get(route('invoices.show', $otherInvoice))
        ->assertNotFound();

    $this->actingAs($this->owner)
        ->post(route('invoices.addPayment', $otherInvoice), [
            'amount' => 10,
        ])
        ->assertNotFound();
});
