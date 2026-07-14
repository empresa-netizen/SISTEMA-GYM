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
        ['title' => 'Saque padrao', 'value' => 'Ate 1 dia util', 'hint' => 'Prazo visual usado no clone MGTEAM.'],
    ];
@endphp

@push('styles')
<style>
    .mg-account-page {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .mg-account-hero {
        position: relative;
        overflow: hidden;
        padding: clamp(1.15rem, 3vw, 1.6rem);
        background:
            radial-gradient(circle at top right, rgba(59, 130, 246, 0.26), transparent 36%),
            linear-gradient(135deg, rgba(17, 24, 39, 0.98), rgba(5, 7, 10, 0.98));
    }

    .mg-account-hero__inner,
    .mg-account-card__top,
    .mg-account-plan__top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
    }

    .mg-account-card-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.75rem;
    }

    .mg-account-card,
    .mg-account-fee-card,
    .mg-account-plan-option {
        padding: 0.95rem;
        border: 1px solid var(--mg-border);
        border-radius: 1rem;
        background: linear-gradient(150deg, rgba(30, 41, 59, 0.72), rgba(12, 16, 24, 0.94));
    }

    .mg-account-card__icon {
        width: 2.35rem;
        height: 2.35rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.8rem;
        background: var(--mg-blue-soft);
        color: var(--mg-blue);
        font-size: 1.15rem;
    }

    .mg-account-value {
        margin-top: 0.8rem;
        color: var(--mg-text);
        font-size: 1.25rem;
        font-weight: 800;
        letter-spacing: -0.03em;
    }

    .mg-account-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(20rem, 0.65fr);
        gap: 1rem;
    }

    .mg-account-fees {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.75rem;
    }

    .mg-account-plan-price {
        color: var(--mg-text);
        font-size: clamp(1.8rem, 4vw, 2.6rem);
        font-weight: 850;
        letter-spacing: -0.05em;
    }

    .mg-account-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .mg-account-plan-list {
        display: grid;
        gap: 0.75rem;
    }

    .mg-account-plan-option {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
    }

    @media (max-width: 1199.98px) {
        .mg-account-card-grid,
        .mg-account-fees {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .mg-account-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .mg-account-card-grid,
        .mg-account-fees {
            grid-template-columns: 1fr;
        }

        .mg-account-hero__inner,
        .mg-account-card__top,
        .mg-account-plan__top,
        .mg-account-plan-option {
            flex-direction: column;
        }
    }
</style>
@endpush

@section('content')
<div class="mg-account-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Minha Conta</h1>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter mg-clients-counter--delivered">
                    <i class="ri-user-heart-line"></i>
                    {{ $memberStats['active'] }} clientes ativos
                </span>
                <span class="mg-clients-counter">
                    <i class="ri-database-2-line"></i>
                    Dados locais
                </span>
            </div>
        </div>
        <div class="mg-clients-toolbar__right">
            <a href="{{ route('account.settings') }}" class="mg-btn-ghost">
                <i class="ri-settings-3-line"></i> Configuracoes
            </a>
            <a href="{{ route('profile') }}" class="mg-btn-primary">
                <i class="ri-user-settings-line"></i> Editar perfil
            </a>
        </div>
    </div>

    <section class="mg-panel mg-account-hero">
        <div class="mg-account-hero__inner">
            <div>
                <span class="mg-section-pill">Informacoes da conta</span>
                <h2 class="mg-section-title h4 mb-1 mt-2">{{ $user->name ?? config('brand.short', 'Coach') }}</h2>
                <p class="mg-page-sub mb-0">Conta autenticada localmente para {{ config('brand.name', 'MGTEAM FITNESS & HEALTH') }}.</p>
            </div>
            <span class="mg-status-badge {{ $statusClass }}">
                <i class="ri-shield-check-line"></i>
                {{ $statusLabels[$accountStatus] ?? ucfirst($accountStatus) }}
                <span class="visually-hidden">{{ ucfirst($accountStatus) }}</span>
            </span>
        </div>
    </section>

    <section class="mg-account-card-grid">
        <div class="mg-account-card">
            <div class="mg-account-card__top">
                <div class="mg-panel-label">Registro</div>
                <span class="mg-account-card__icon"><i class="ri-calendar-check-line"></i></span>
            </div>
            <div class="mg-account-value">{{ $user->created_at?->format('d/m/Y') ?? 'Local' }}</div>
            <p class="mg-panel-hint mb-0">Criacao do usuario Auth.</p>
        </div>
        <div class="mg-account-card">
            <div class="mg-account-card__top">
                <div class="mg-panel-label">E-mail</div>
                <span class="mg-account-card__icon"><i class="ri-mail-line"></i></span>
            </div>
            <div class="mg-account-value fs-6 text-break">{{ $user->email }}</div>
            <p class="mg-panel-hint mb-0">Login local da conta.</p>
        </div>
        <div class="mg-account-card">
            <div class="mg-account-card__top">
                <div class="mg-panel-label">Clientes ativos</div>
                <span class="mg-account-card__icon"><i class="ri-team-line"></i></span>
            </div>
            <div class="mg-account-value">{{ $memberStats['active'] }}</div>
            <p class="mg-panel-hint mb-0">{{ $memberStats['total'] }} clientes cadastrados.</p>
        </div>
        <div class="mg-account-card">
            <div class="mg-account-card__top">
                <div class="mg-panel-label">Termos</div>
                <span class="mg-account-card__icon"><i class="ri-file-shield-2-line"></i></span>
            </div>
            <div class="mg-account-value fs-6">Termos locais</div>
            <p class="mg-panel-hint mb-0">Aceite externo nao sincronizado.</p>
        </div>
    </section>

    <section class="mg-panel mg-panel--compact">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
            <div>
                <span class="mg-section-pill">Taxas e Prazos</span>
                <h2 class="mg-section-title h5 mb-0 mt-2">Referencias locais de cobranca</h2>
            </div>
            <span class="mg-chip mg-chip--info">Sem API externa</span>
        </div>
        <div class="mg-account-fees">
            @foreach($feeCards as $card)
                <div class="mg-account-fee-card">
                    <div class="mg-panel-label">{{ $card['title'] }}</div>
                    <div class="mg-account-value fs-5">{{ $card['value'] }}</div>
                    <p class="mg-panel-hint mb-0">{{ $card['hint'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <div class="mg-account-grid">
        <section class="mg-panel">
            <div class="mg-account-plan__top mb-3">
                <div>
                    <span class="mg-section-pill">Plano de assinatura</span>
                    <h2 class="mg-section-title h4 mb-1 mt-2">{{ $accountPlanName }}</h2>
                    <p class="mg-page-sub mb-0">{{ $accountPlanDescription }}</p>
                </div>
                <span class="mg-status-badge {{ $statusClass }}">{{ $statusLabels[$accountStatus] ?? ucfirst($accountStatus) }}</span>
            </div>

            <div class="mg-account-plan-price">
                {{ $accountPlanPrice ?? 'Plano local' }}
                @if($subscription?->plan)
                    <span class="fs-13 text-muted">/ {{ $subscription->plan->duration_days }} dias</span>
                @endif
            </div>

            <div class="row g-2 my-3">
                <div class="col-sm-4">
                    <div class="mg-account-fee-card h-100">
                        <div class="mg-panel-label">Inicio</div>
                        <div class="mg-account-value fs-6">{{ $subscription?->start_date?->format('d/m/Y') ?? 'Nao registrado' }}</div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="mg-account-fee-card h-100">
                        <div class="mg-panel-label">Renovacao</div>
                        <div class="mg-account-value fs-6">{{ $billingEndDate?->format('d/m/Y') ?? 'Nao registrada' }}</div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="mg-account-fee-card h-100">
                        <div class="mg-panel-label">Clientes vencidos</div>
                        <div class="mg-account-value fs-6">{{ $memberStats['expired'] }}</div>
                    </div>
                </div>
            </div>

            <div class="mg-account-actions">
                <button type="button" class="mg-btn-ghost" disabled title="Stub local: cancelamento externo desativado">
                    <i class="ri-close-circle-line"></i> Cancelar
                </button>
                <a href="{{ route('subscriptions.index') }}" class="mg-btn-primary">
                    <i class="ri-swap-line"></i> Alterar plano
                </a>
            </div>
        </section>

        <aside class="mg-panel mg-panel--compact">
            <span class="mg-section-pill">Forma de pagamento</span>
            <h2 class="mg-section-title h5 mb-2 mt-2">{{ $gatewayLabel }}</h2>
            <p class="mg-panel-hint">Metodo salvo na assinatura local quando existir. Nenhuma chamada de gateway e feita por esta tela.</p>
            <div class="mg-help-row">
                <span>Renovacao automatica</span>
                <span class="mg-chip {{ $subscription?->auto_renew ? 'mg-chip--success' : '' }}">
                    {{ $subscription?->auto_renew ? 'Ativa' : 'Local nao conectado' }}
                </span>
            </div>
            <div class="mg-help-row">
                <span>ID local da assinatura</span>
                <strong>{{ $subscription?->id ? '#'.$subscription->id : 'Nao criada' }}</strong>
            </div>
        </aside>
    </div>

    <section class="mg-panel mg-panel--compact">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
            <div>
                <span class="mg-section-pill">Planos disponiveis</span>
                <h2 class="mg-section-title h5 mb-0 mt-2">Produtos de assinatura locais</h2>
            </div>
            <span class="mg-chip">{{ $plans->count() }} planos</span>
        </div>

        <div class="mg-account-plan-list">
            @forelse($plans as $plan)
                <div class="mg-account-plan-option">
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <strong>{{ $plan->name }}</strong>
                            @if($plan->is_featured)
                                <span class="mg-chip mg-chip--success">Destaque</span>
                            @endif
                        </div>
                        <p class="mg-panel-hint mb-0">{{ $plan->description ?: 'Plano local sem descricao.' }}</p>
                    </div>
                    <div class="text-sm-end">
                        <div class="mg-account-value fs-5 mt-0">{{ $money($plan->price) }}</div>
                        <small class="text-muted">{{ $plan->duration_days }} dias @if($plan->max_members) - ate {{ $plan->max_members }} clientes @endif</small>
                    </div>
                </div>
            @empty
                <div class="mg-empty-state mg-empty-state--compact">
                    <i class="ri-price-tag-3-line"></i>
                    <p>Nenhum plano de assinatura local ativo.</p>
                </div>
            @endforelse
        </div>
    </section>

    @if($transactions->isNotEmpty())
        <section class="mg-panel mg-panel--compact">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-2">
                <div>
                    <span class="mg-section-pill">Historico</span>
                    <h2 class="mg-section-title h5 mb-0 mt-2">Transacoes locais</h2>
                </div>
                <span class="mg-chip">{{ $transactions->count() }} registros</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle mg-table-compact">
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
                                    <span class="mg-chip @if($transaction->status === 'completed') mg-chip--success @elseif($transaction->status === 'failed') mg-chip--danger @else mg-chip--warn @endif">
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
