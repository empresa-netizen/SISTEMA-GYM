<?php

use Laravel\Sanctum\Sanctum;

describe('API V1 Finance contract', function () {
    it('lists invoices with InvoiceResource fields and pagination', function () {
        $owner = createOwner();
        $member = createMemberFor($owner, ['email' => 'finance.member@test.app']);
        createInvoiceFor($owner, $member, [
            'total_amount' => 250.50,
            'paid_amount' => 50.50,
            'status' => 'partially_paid',
        ]);

        Sanctum::actingAs($owner);

        $this->getJson('/api/v1/finance/invoices?per_page=20')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'invoice_number',
                        'member_id',
                        'invoice_date',
                        'due_date',
                        'subtotal',
                        'tax_amount',
                        'discount_amount',
                        'total_amount',
                        'paid_amount',
                        'balance',
                        'status',
                        'notes',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ])
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.member_id', $member->id)
            ->assertJsonPath('data.0.status', 'partially_paid')
            ->assertJsonPath('data.0.balance', 200);
    });

    it('shows a single invoice with nested member and payments keys', function () {
        $owner = createOwner();
        $member = createMemberFor($owner, ['email' => 'invoice.show@test.app']);
        $invoice = createInvoiceFor($owner, $member, [
            'total_amount' => 100,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);

        Sanctum::actingAs($owner);

        $this->getJson('/api/v1/finance/invoices/'.$invoice->id)
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'invoice_number',
                    'member_id',
                    'total_amount',
                    'paid_amount',
                    'balance',
                    'status',
                    'member',
                    'payments',
                    'created_at',
                ],
            ])
            ->assertJsonPath('data.id', $invoice->id)
            ->assertJsonPath('data.balance', 100);
    });

    it('returns finance dashboard kpi contract', function () {
        $owner = createOwner();
        Sanctum::actingAs($owner);

        $this->getJson('/api/v1/finance/dashboard')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'available_balance',
                    'pending_balance',
                    'month_revenue',
                    'month_transactions',
                    'recent_payments',
                ],
            ]);
    });
});
