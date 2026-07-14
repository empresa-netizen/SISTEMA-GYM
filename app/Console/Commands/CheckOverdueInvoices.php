<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Notifications\InAppAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class CheckOverdueInvoices extends Command
{
    protected $signature = 'finance:check-overdue {--dry-run : Apenas lista sem alterar status}';

    protected $description = 'Marca faturas vencidas em aberto como overdue e notifica o coach responsável';

    public function handle(): int
    {
        $invoices = Invoice::withoutGlobalScopes()
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->whereDate('due_date', '<', now()->toDateString())
            ->with(['member.user', 'parent'])
            ->get();

        if ($invoices->isEmpty()) {
            $this->info('Nenhuma fatura vencida pendente de transição.');

            return self::SUCCESS;
        }

        $updated = 0;

        foreach ($invoices as $invoice) {
            $this->line("#{$invoice->id} {$invoice->invoice_number} — due {$invoice->due_date?->toDateString()}");

            if ($this->option('dry-run')) {
                continue;
            }

            $invoice->forceFill(['status' => 'overdue'])->save();
            $updated++;

            $title = 'Fatura atrasada';
            $body = sprintf(
                'A fatura %s de %s venceu em %s (saldo R$ %s).',
                $invoice->invoice_number,
                $invoice->member?->name ?? 'cliente',
                $invoice->due_date?->format('d/m/Y') ?? '—',
                number_format((float) $invoice->remaining_balance, 2, ',', '.')
            );
            $url = url('/invoices/'.$invoice->id.'/show');

            $recipients = collect([
                $invoice->parent,
                $invoice->member?->user,
            ])->filter()->unique('id');

            foreach ($recipients as $recipient) {
                Notification::send($recipient, new InAppAlert(
                    title: $title,
                    body: $body,
                    url: $url,
                    icon: 'ri-error-warning-line',
                    level: 'danger',
                ));
            }
        }

        $this->info($this->option('dry-run')
            ? "Dry-run: {$invoices->count()} fatura(s) elegíveis."
            : "Atualizadas: {$updated} fatura(s) → overdue + notificação.");

        return self::SUCCESS;
    }
}
