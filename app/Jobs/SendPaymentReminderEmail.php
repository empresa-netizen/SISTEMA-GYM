<?php

namespace App\Jobs;

use App\Mail\Common;
use App\Models\Invoice;
use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPaymentReminderEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $invoiceId,
        public ?string $customSubject = null,
        public ?string $customMessage = null,
    ) {}

    public function handle(): void
    {
        $invoice = Invoice::query()->with('member')->find($this->invoiceId);

        if (! $invoice || ! $invoice->member?->email) {
            Log::warning('SendPaymentReminderEmail: fatura ou e-mail ausente', [
                'invoice_id' => $this->invoiceId,
            ]);

            return;
        }

        /** @var Member $member */
        $member = $invoice->member;
        $balance = (float) $invoice->total_amount - (float) $invoice->paid_amount;
        $due = optional($invoice->due_date)?->format('d/m/Y') ?? '—';

        $subject = $this->customSubject ?: "Lembrete de pagamento — fatura {$invoice->invoice_number}";
        $message = $this->customMessage ?: implode("\n", [
            "Ola, {$member->name}!",
            '',
            "Este e um lembrete sobre a fatura {$invoice->invoice_number}.",
            'Valor em aberto: R$ '.number_format($balance, 2, ',', '.'),
            "Vencimento: {$due}",
            '',
            'Qualquer duvida, fale com seu coach.',
        ]);

        Mail::to($member->email)->send(new Common($subject, $message));
    }
}
