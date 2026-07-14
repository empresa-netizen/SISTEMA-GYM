<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\User;
use App\Notifications\InAppAlert;
use Illuminate\Database\Seeder;

class MgteamNotificationSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::role('owner')->first();
        if (! $owner) {
            return;
        }

        $owner->notifications()->delete();

        $owner->notify(new InAppAlert(
            title: 'Bem-vindo ao painel MGTEAM',
            body: 'Seu dashboard e notificações in-app estão ativos no ambiente local.',
            url: route('dashboard'),
            icon: 'ri-rocket-line',
            level: 'info',
        ));

        $overdue = Invoice::where('parent_id', $owner->id)
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->whereDate('due_date', '<', now()->toDateString())
            ->with('member')
            ->take(3)
            ->get();

        foreach ($overdue as $invoice) {
            $owner->notify(new InAppAlert(
                title: 'Fatura em atraso',
                body: ($invoice->member?->name ?? 'Cliente').' — '.$invoice->invoice_number.' venceu em '.$invoice->due_date?->format('d/m/Y').'.',
                url: route('finance.index', ['tab' => 'transactions', 'status' => 'overdue']),
                icon: 'ri-error-warning-line',
                level: 'danger',
            ));
        }

        $owner->notify(new InAppAlert(
            title: 'Feed com interações',
            body: 'Curtidas e comentários no feed geram alertas para o coach.',
            url: route('feed.index'),
            icon: 'ri-heart-line',
            level: 'success',
        ));

        $this->command?->info('✅ Notificações in-app seedadas para o owner');
    }
}
