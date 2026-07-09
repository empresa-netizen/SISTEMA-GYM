@extends('layouts.master')

@section('title', 'Minha Conta')

@php
    $money = fn ($value) => 'R$ '.number_format((float) $value, 2, ',', '.');
    $accountPlanName = $subscription?->plan?->name ?? $user->subscription ?? 'Sem plano ativo';
    $accountPlanDescription = $subscription?->plan?->description ?? 'Assinatura da conta local do coach.';
    $accountPlanPrice = $subscription?->plan ? $money($subscription->plan->price) : null;
    $accountStatus = $subscription?->status ?? ($user->subscription ? 'active' : 'inactive');
    $statusLabels = [
        'active' => 'Ativa',
        'trial' => 'Teste',
        'cancelled' => 'Cancelada',
        'expired' => 'Expirada',
        'inactive' => 'Inativa',
    ];
    $statusClass = match ($accountStatus) {
        'active' => 'is-ok',
        'trial' => 'is-warn',
        default => 'is-missing',
    };
    $gatewayLabel = $subscription?->payment_gateway ? strtoupper($subscription->payment_gateway) : 'Forma local nao conectada';
    $billingEndDate = $subscription?->end_date ?? $user->subscription_expire_date;
    $transactions = $subscription?->transactions ?? collect();
    $feeCards = [
        ['title' => 'Cartao de credito', 'value' => '1% ou min. R$ 1,99', 'hint' => 'Referencia local para simulacao de taxas.'],
        ['title' => 'Pix', 'value' => 'Disponivel na hora', 'hint' => 'Registro local sem conciliacao externa.'],
        ['title' => 'Saque padrao', 'value' => 'Ate 1 dia util', 'hint' => 'Prazo visual usado no clone Prime.'],
    ];
@endphp

@push('styles')
<style>
    .prime-account-page {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .prime-account-hero {
        position: relative;
        overflow: hidden;
        padding: clamp(1.15rem, 3vw, 1.6rem);
        background:
            radial-gradient(circle at top right, rgba(59, 130, 246, 0.26), transparent 36%),
            linear-gradient(135deg, rgba(17, 24, 39, 0.98), rgba(5, 7, 10, 0.98));
    }

    .prime-account-hero__inner,
    .prime-account-card__top,
    .prime-account-plan__top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
    }

    .prime-account-card-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.75rem;
    }

    .prime-account-card,
    .prime-account-fee-card,
    .prime-account-plan-option {
        padding: 0.95rem;
        border: 1px solid var(--prime-border);
        border-radius: 1rem;
        background: linear-gradient(150deg, rgba(30, 41, 59, 0.72), rgba(12, 16, 24, 0.94));
    }

    .prime-account-card__icon {
        width: 2.35rem;
        height: 2.35rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.8rem;
        background: var(--prime-blue-soft);
        color: var(--prime-blue);
        font-size: 1.15rem;
    }

    .prime-account-value {
        margin-top: 0.8rem;
        color: var(--prime-text);
        font-size: 1.25rem;
        font-weight: 800;
        letter-spacing: -0.03em;
    }

    .prime-account-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(20rem, 0.65fr);
        gap: 1rem;
    }

    .prime-account-fees {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.75rem;
    }

    .prime-account-plan-price {
        color: var(--prime-text);
        font-size: clamp(1.8rem, 4vw, 2.6rem);
        font-weight: 850;
        letter-spacing: -0.05em;
    }

    .prime-account-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .prime-account-plan-list {
        display: grid;
        gap: 0.75rem;
    }

    .prime-account-plan-option {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
    }

    @media (max-width: 1199.98px) {
        .prime-account-card-grid,
        .prime-account-fees {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .prime-account-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .prime-account-card-grid,
        .prime-account-fees {
            grid-template-columns: 1fr;
        }

        .prime-account-hero__inner,
        .prime-account-card__top,
        .prime-account-plan__top,
        .prime-account-plan-option {
            flex-direction: column;
        }
    }
</style>
@endpush

@section('content')
<div class="prime-account-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Minha Conta</h1>
            <div class="prime-clients-counters">
                <span class="prime-clients-counter prime-clients-counter--delivered">
                    <i class="ri-user-heart-line"></i>
                    {{ $memberStats['active'] }} clientes ativos
                </span>
                <span class="prime-clients-counter">
                    <i class="ri-database-2-line"></i>
                    Dados locais
                </span>
            </div>
        </div>
        <div class="prime-clients-toolbar__right">
            <a href="{{ route('account.settings') }}" class="prime-btn-ghost">
                <i class="ri-settings-3-line"></i> Configuracoes
            </a>
            <a href="{{ route('profile') }}" class="prime-btn-primary">
                <i class="ri-user-settings-line"></i> Editar perfil
            </a>
        </div>
    </div>

    <section class="prime-panel prime-account-hero">
        <div class="prime-account-hero__inner">
            <div>
                <span class="prime-section-pill">Informacoes da conta</span>
                <h2 class="prime-section-title h4 mb-1 mt-2">{{ $user->name ?? config('brand.short', 'Coach') }}</h2>
                <p class="prime-page-sub mb-0">Conta autenticada localmente para {{ config('brand.name', 'MGTEAM FITNESS & HEALTH') }}.</p>
            </div>
            <span class="prime-status-badge {{ $statusClass }}">
                <i class="ri-shield-check-line"></i>
                {{ $statusLabels[$accountStatus] ?? ucfirst($accountStatus) }}
            </span>
        </div>
    </section>

    <section class="prime-account-card-grid">
        <div class="prime-account-card">
            <div class="prime-account-card__top">
                <div class="prime-panel-label">Registro</div>
                <span class="prime-account-card__icon"><i class="ri-calendar-check-line"></i></span>
            </div>
            <div class="prime-account-value">{{ $user->created_at?->format('d/m/Y') ?? 'Local' }}</div>
            <p class="prime-panel-hint mb-0">Criacao do usuario Auth.</p>
        </div>
        <div class="prime-account-card">
            <div class="prime-account-card__top">
                <div class="prime-panel-label">E-mail</div>
                <span class="prime-account-card__icon"><i class="ri-mail-line"></i></span>
            </div>
            <div class="prime-account-value fs-6 text-break">{{ $user->email }}</div>
            <p class="prime-panel-hint mb-0">Login local da conta.</p>
        </div>
        <div class="prime-account-card">
            <div class="prime-account-card__top">
                <div class="prime-panel-label">Clientes ativos</div>
                <span class="prime-account-card__icon"><i class="ri-team-line"></i></span>
            </div>
            <div class="prime-account-value">{{ $memberStats['active'] }}</div>
            <p class="prime-panel-hint mb-0">{{ $memberStats['total'] }} clientes cadastrados.</p>
        </div>
        <div class="prime-account-card">
            <div class="prime-account-card__top">
                <div class="prime-panel-label">Termos</div>
                <span class="prime-account-card__icon"><i class="ri-file-shield-2-line"></i></span>
            </div>
            <div class="prime-account-value fs-6">Termos locais</div>
            <p class="prime-panel-hint mb-0">Aceite externo nao sincronizado.</p>
        </div>
    </section>

    <section class="prime-panel prime-panel--compact">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
            <div>
                <span class="prime-section-pill">Taxas e Prazos</span>
                <h2 class="prime-section-title h5 mb-0 mt-2">Referencias locais de cobranca</h2>
            </div>
            <span class="prime-chip prime-chip--info">Sem API externa</span>
        </div>
        <div class="prime-account-fees">
            @foreach($feeCards as $card)
                <div class="prime-account-fee-card">
                    <div class="prime-panel-label">{{ $card['title'] }}</div>
                    <div class="prime-account-value fs-5">{{ $card['value'] }}</div>
                    <p class="prime-panel-hint mb-0">{{ $card['hint'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <div class="prime-account-grid">
        <section class="prime-panel">
            <div class="prime-account-plan__top mb-3">
                <div>
                    <span class="prime-section-pill">Plano de assinatura</span>
                    <h2 class="prime-section-title h4 mb-1 mt-2">{{ $accountPlanName }}</h2>
                    <p class="prime-page-sub mb-0">{{ $accountPlanDescription }}</p>
                </div>
                <span class="prime-status-badge {{ $statusClass }}">{{ $statusLabels[$accountStatus] ?? ucfirst($accountStatus) }}</span>
            </div>

            <div class="prime-account-plan-price">
                {{ $accountPlanPrice ?? 'Plano local' }}
                @if($subscription?->plan)
                    <span class="fs-13 text-muted">/ {{ $subscription->plan->duration_days }} dias</span>
                @endif
            </div>

            <div class="row g-2 my-3">
                <div class="col-sm-4">
                    <div class="prime-account-fee-card h-100">
                        <div class="prime-panel-label">Inicio</div>
                        <div class="prime-account-value fs-6">{{ $subscription?->start_date?->format('d/m/Y') ?? 'Nao registrado' }}</div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="prime-account-fee-card h-100">
                        <div class="prime-panel-label">Renovacao</div>
                        <div class="prime-account-value fs-6">{{ $billingEndDate?->format('d/m/Y') ?? 'Nao registrada' }}</div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="prime-account-fee-card h-100">
                        <div class="prime-panel-label">Clientes vencidos</div>
                        <div class="prime-account-value fs-6">{{ $memberStats['expired'] }}</div>
                    </div>
                </div>
            </div>

            <div class="prime-account-actions">
                <button type="button" class="prime-btn-ghost" disabled title="Stub local: cancelamento externo desativado">
                    <i class="ri-close-circle-line"></i> Cancelar
                </button>
                <a href="{{ route('subscriptions.index') }}" class="prime-btn-primary">
                    <i class="ri-swap-line"></i> Alterar plano
                </a>
            </div>
        </section>

        <aside class="prime-panel prime-panel--compact">
            <span class="prime-section-pill">Forma de pagamento</span>
            <h2 class="prime-section-title h5 mb-2 mt-2">{{ $gatewayLabel }}</h2>
            <p class="prime-panel-hint">Metodo salvo na assinatura local quando existir. Nenhuma chamada de gateway e feita por esta tela.</p>
            <div class="prime-help-row">
                <span>Renovacao automatica</span>
                <span class="prime-chip {{ $subscription?->auto_renew ? 'prime-chip--success' : '' }}">
                    {{ $subscription?->auto_renew ? 'Ativa' : 'Local nao conectado' }}
                </span>
            </div>
            <div class="prime-help-row">
                <span>ID local da assinatura</span>
                <strong>{{ $subscription?->id ? '#'.$subscription->id : 'Nao criada' }}</strong>
            </div>
        </aside>
    </div>

    <section class="prime-panel prime-panel--compact">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
            <div>
                <span class="prime-section-pill">Planos disponiveis</span>
                <h2 class="prime-section-title h5 mb-0 mt-2">Produtos de assinatura locais</h2>
            </div>
            <span class="prime-chip">{{ $plans->count() }} planos</span>
        </div>

        <div class="prime-account-plan-list">
            @forelse($plans as $plan)
                <div class="prime-account-plan-option">
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <strong>{{ $plan->name }}</strong>
                            @if($plan->is_featured)
                                <span class="prime-chip prime-chip--success">Destaque</span>
                            @endif
                        </div>
                        <p class="prime-panel-hint mb-0">{{ $plan->description ?: 'Plano local sem descricao.' }}</p>
                    </div>
                    <div class="text-sm-end">
                        <div class="prime-account-value fs-5 mt-0">{{ $money($plan->price) }}</div>
                        <small class="text-muted">{{ $plan->duration_days }} dias @if($plan->max_members) - ate {{ $plan->max_members }} clientes @endif</small>
                    </div>
                </div>
            @empty
                <div class="prime-empty-state prime-empty-state--compact">
                    <i class="ri-price-tag-3-line"></i>
                    <p>Nenhum plano de assinatura local ativo.</p>
                </div>
            @endforelse
        </div>
    </section>

    @if($transactions->isNotEmpty())
        <section class="prime-panel prime-panel--compact">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-2">
                <div>
                    <span class="prime-section-pill">Historico</span>
                    <h2 class="prime-section-title h5 mb-0 mt-2">Transacoes locais</h2>
                </div>
                <span class="prime-chip">{{ $transactions->count() }} registros</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle prime-table-compact">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Transacao</th>
                            <th>Metodo</th>
                            <th class="text-end">Valor</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                            <tr>
                                <td>{{ ($transaction->paid_at ?? $transaction->created_at)?->format('d/m/Y H:i') }}</td>
                                <td>{{ $transaction->transaction_id }}</td>
                                <td>{{ strtoupper($transaction->payment_gateway ?? '-') }}</td>
                                <td class="text-end">{{ $money($transaction->amount) }}</td>
                                <td>
                                    <span class="prime-chip @if($transaction->status === 'completed') prime-chip--success @elseif($transaction->status === 'failed') prime-chip--danger @else prime-chip--warn @endif">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif
</div>
@endsection
