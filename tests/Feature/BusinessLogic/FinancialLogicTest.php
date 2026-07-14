<?php

use App\Models\Invoice;
use App\Notifications\InAppAlert;
use Illuminate\Support\Facades\Notification;

describe('Financial business logic', function () {
    it('marks past-due unpaid invoices as overdue and notifies the owner', function () {
        Notification::fake();

        $owner = createOwner(['email' => 'finance.owner@test.app']);
        $member = createMemberFor($owner, [
            'name' => 'Aluno Financeiro',
            'email' => 'aluno.finance@test.app',
        ]);

        $invoice = createInvoiceFor($owner, $member, [
            'due_date' => now()->subDay()->toDateString(),
            'status' => 'unpaid',
            'total_amount' => 197,
            'paid_amount' => 0,
        ]);

        $this->artisan('finance:check-overdue')
            ->assertSuccessful();

        expect($invoice->fresh()->status)->toBe('overdue')
            ->and($invoice->fresh()->isOverdue())->toBeTrue();

        Notification::assertSentTo($owner, InAppAlert::class, function (InAppAlert $notification) use ($invoice) {
            return str_contains($notification->title, 'atrasada')
                && str_contains($notification->body, $invoice->invoice_number);
        });
    });

    it('does not flag paid invoices as overdue', function () {
        Notification::fake();

        $owner = createOwner(['email' => 'finance.paid@test.app']);
        $member = createMemberFor($owner, ['email' => 'aluno.paid@test.app']);
        $invoice = createInvoiceFor($owner, $member, [
            'due_date' => now()->subDays(3)->toDateString(),
            'status' => 'paid',
            'paid_amount' => 100,
            'total_amount' => 100,
        ]);

        $this->artisan('finance:check-overdue')->assertSuccessful();

        expect($invoice->fresh()->status)->toBe('paid');
        Notification::assertNothingSentTo($owner);
    });

    it('rejects paid invoices without payment trail in domain audit', function () {
        $owner = createOwner(['email' => 'finance.audit@test.app']);
        $member = createMemberFor($owner, ['email' => 'aluno.audit@test.app']);

        Invoice::withoutGlobalScopes()->create([
            'parent_id' => $owner->id,
            'member_id' => $member->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->toDateString(),
            'subtotal' => 50,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 50,
            'paid_amount' => 50,
            'status' => 'paid',
        ]);

        $this->artisan('mgteam:audit')->assertFailed();
    });
});
