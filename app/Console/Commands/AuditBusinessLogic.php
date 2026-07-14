<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Member;
use App\Models\MemberNote;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class AuditBusinessLogic extends Command
{
    protected $signature = 'mgteam:audit';

    protected $description = 'Auditoria profunda da lógica de negócios e integridade relacional do sistema.';

    private int $failures = 0;

    private int $warnings = 0;

    public function handle(): int
    {
        $this->info('Iniciando Auditoria de Regras de Negócio (MGTEAM)...');

        $this->checkFinancialIntegrity();
        $this->checkRolesAndPermissions();
        $this->checkPrescriptionLogic();
        $this->checkOrphanRecords();

        $this->newLine();
        if ($this->failures > 0) {
            $this->error("Auditoria finalizada com {$this->failures} falha(s) e {$this->warnings} aviso(s).");

            return self::FAILURE;
        }

        if ($this->warnings > 0) {
            $this->warn("Auditoria finalizada com {$this->warnings} aviso(s) — sem falhas críticas.");

            return self::SUCCESS;
        }

        $this->info('Auditoria finalizada limpa. Integridade de domínio OK.');

        return self::SUCCESS;
    }

    private function checkFinancialIntegrity(): void
    {
        $this->warn('--- Verificando Integridade Financeira ---');

        // Domínio real: unpaid/partially_paid (não "pending"). overdue = status explícito pós finance:check-overdue.
        $staleOpen = Invoice::withoutGlobalScopes()
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->whereDate('due_date', '<', now()->toDateString())
            ->count();

        if ($staleOpen > 0) {
            $this->failCritical(
                "ALERTA: Existem {$staleOpen} faturas vencidas ainda em unpaid/partially_paid. ".
                'Rode `php artisan finance:check-overdue` — o Job de transição está falhando ou não rodou.'
            );
        } else {
            $this->line('OK: Transições de status de faturas vencidas estão coerentes.');
        }

        $paidWithoutTransaction = Invoice::withoutGlobalScopes()
            ->where('status', 'paid')
            ->doesntHave('payments')
            ->where('paid_amount', '>', 0)
            ->count();

        if ($paidWithoutTransaction > 0) {
            $this->failCritical(
                "FALHA CRÍTICA: {$paidWithoutTransaction} faturas 'paid' com paid_amount > 0 sem registro em invoice_payments. ".
                'Risco de fraude ou erro na baixa manual/webhook.'
            );
        } else {
            $this->line('OK: Faturas pagas possuem trilha de pagamento coerente.');
        }
    }

    private function checkRolesAndPermissions(): void
    {
        $this->warn('--- Verificando Permissões (Spatie) ---');

        $owners = User::role('owner')->count();
        if ($owners === 0) {
            $this->failCritical("FALHA: Sistema não possui nenhum 'Owner' (Dono) configurado.");
        } else {
            $this->line("OK: Hierarquia de donos detectada ({$owners}).");
        }
    }

    private function checkPrescriptionLogic(): void
    {
        $this->warn('--- Verificando Lógica de Prescrição (Treino/Dieta/Cardio) ---');

        $activeWithoutPlans = Member::withoutGlobalScopes()
            ->where('status', 'active')
            ->whereDoesntHave('workouts')
            ->whereDoesntHave('dietPrescriptions')
            ->whereDoesntHave('cardioPlans')
            ->count();

        if ($activeWithoutPlans > 0) {
            $this->warnIssue(
                "AVISO: {$activeWithoutPlans} clientes ativos sem treino, dieta ou cardio prescritos. ".
                'Verifique o fluxo de onboarding/prescrição.'
            );
        } else {
            $this->line('OK: Fluxo de prescrição associativa está funcionando.');
        }
    }

    private function checkOrphanRecords(): void
    {
        $this->warn('--- Verificando Integridade Relacional (Registros Órfãos) ---');

        if (! Schema::hasTable('member_notes')) {
            $this->line('OK: Tabela member_notes ausente neste ambiente — pulando.');

            return;
        }

        $orphanNotes = MemberNote::query()
            ->whereDoesntHave('member')
            ->count();

        if ($orphanNotes > 0) {
            $this->failCritical(
                "VAZAMENTO DE DADOS: {$orphanNotes} notas pertencem a clientes inexistentes. ".
                "Falta onDelete('cascade') ou limpeza pós-delete."
            );
        } else {
            $this->line('OK: Banco de dados relacional limpo e em cascata.');
        }
    }

    private function failCritical(string $message): void
    {
        $this->failures++;
        $this->error($message);
    }

    private function warnIssue(string $message): void
    {
        $this->warnings++;
        $this->error($message);
    }
}
