<?php

namespace App\Console\Commands;

use App\Jobs\SendPaymentReminderEmail;
use App\Models\Invoice;
use Illuminate\Console\Command;

class SendOverduePaymentReminders extends Command
{
    protected $signature = 'payments:send-reminders {--dry-run : Apenas lista faturas sem enfileirar}';

    protected $description = 'Enfileira e-mails de lembrete para faturas vencidas em aberto';

    public function handle(): int
    {
        $invoices = Invoice::query()
            ->whereIn('status', ['unpaid', 'partially_paid', 'overdue'])
            ->whereDate('due_date', '<', now()->toDateString())
            ->with('member:id,name,email')
            ->get();

        if ($invoices->isEmpty()) {
            $this->info('Nenhuma fatura vencida em aberto.');

            return self::SUCCESS;
        }

        foreach ($invoices as $invoice) {
            $this->line("#{$invoice->id} {$invoice->invoice_number} — {$invoice->member?->email}");

            if (! $this->option('dry-run')) {
                SendPaymentReminderEmail::dispatch($invoice->id);
            }
        }

        $this->info($this->option('dry-run')
            ? "Dry-run: {$invoices->count()} fatura(s)."
            : "Enfileirado: {$invoices->count()} lembrete(s).");

        return self::SUCCESS;
    }
}
