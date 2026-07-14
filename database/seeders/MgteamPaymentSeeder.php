<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Seeder;

class MgteamPaymentSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::role('owner')->first();
        if (! $owner) {
            return;
        }

        $ana = Member::where('parent_id', $owner->id)->where('name', 'like', 'Ana%')->first();
        $carlos = Member::where('parent_id', $owner->id)->where('name', 'like', 'Carlos%')->first();
        $members = collect([$ana, $carlos])->filter()->values();

        if ($members->isEmpty()) {
            $members = Member::where('parent_id', $owner->id)->take(2)->get();
        }

        if ($members->isEmpty()) {
            return;
        }

        $invoiceIds = Invoice::where('parent_id', $owner->id)->pluck('id');
        InvoicePayment::whereIn('invoice_id', $invoiceIds)->delete();
        Invoice::where('parent_id', $owner->id)->delete();

        $scenarios = [
            [
                'member' => $ana ?? $members[0],
                'days_ago' => 2,
                'amount' => 297.00,
                'paid' => 297.00,
                'status' => 'paid',
                'method' => 'bank_transfer',
                'label' => 'Consultoria mensal — Ana Beatriz',
            ],
            [
                'member' => $ana ?? $members[0],
                'days_ago' => 18,
                'amount' => 197.00,
                'paid' => 0,
                'status' => 'unpaid',
                'method' => null,
                'label' => 'Avaliação física — Ana Beatriz',
                'overdue_days' => 5,
            ],
            [
                'member' => $ana ?? $members[0],
                'days_ago' => 40,
                'amount' => 497.00,
                'paid' => 250.00,
                'status' => 'partially_paid',
                'method' => 'card',
                'label' => 'Plano trimestral — Ana Beatriz',
            ],
            [
                'member' => $carlos ?? $members->get(1, $members[0]),
                'days_ago' => 1,
                'amount' => 197.00,
                'paid' => 197.00,
                'status' => 'paid',
                'method' => 'card',
                'label' => 'Renovação mensal — Carlos',
            ],
            [
                'member' => $carlos ?? $members->get(1, $members[0]),
                'days_ago' => 10,
                'amount' => 97.00,
                'paid' => 0,
                'status' => 'unpaid',
                'method' => null,
                'label' => 'Ajuste nutricional — Carlos',
                'overdue_days' => 3,
            ],
            [
                'member' => $carlos ?? $members->get(1, $members[0]),
                'days_ago' => 25,
                'amount' => 397.00,
                'paid' => 397.00,
                'status' => 'paid',
                'method' => 'bank_transfer',
                'label' => 'Pacote performance — Carlos',
            ],
            [
                'member' => $ana ?? $members[0],
                'days_ago' => 0,
                'amount' => 97.00,
                'paid' => 97.00,
                'status' => 'paid',
                'method' => 'cash',
                'label' => 'Sessão avulsa — Ana Beatriz',
            ],
        ];

        foreach ($scenarios as $i => $scenario) {
            $member = $scenario['member'];
            $date = now()->subDays($scenario['days_ago']);
            $due = isset($scenario['overdue_days'])
                ? now()->subDays($scenario['overdue_days'])
                : $date->copy()->addDays(7);

            $invoice = Invoice::create([
                'parent_id' => $owner->id,
                'member_id' => $member->id,
                'invoice_date' => $date,
                'due_date' => $due,
                'subtotal' => $scenario['amount'],
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => $scenario['amount'],
                'paid_amount' => $scenario['paid'],
                'status' => $scenario['status'],
                'notes' => $scenario['label'],
            ]);

            if ($scenario['paid'] > 0 && $scenario['method']) {
                InvoicePayment::create([
                    'invoice_id' => $invoice->id,
                    'payment_date' => $date,
                    'amount' => $scenario['paid'],
                    'payment_method' => $scenario['method'],
                    'reference_number' => 'PIX-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                    'notes' => 'Pagamento confirmado',
                ]);
            }
        }

        $this->command?->info('✅ '.count($scenarios).' faturas financeiras (Ana/Carlos) sincronizadas');
    }
}
