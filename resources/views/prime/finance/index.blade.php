@extends('layouts.master')

@section('title', 'Financeiro')

@section('content')
@php
    $money = fn ($value) => 'R$ '.number_format((float) $value, 2, ',', '.');
    $financeCards = [
        [
            'label' => 'Dashboard',
            'description' => 'Resumo dos saldos e recebíveis',
            'icon' => 'ri-pie-chart-2-line',
            'href' => route('finance.index'),
            'active' => true,
        ],
        [
            'label' => 'Transações',
            'description' => $monthTransactions.' recebimentos neste mês',
            'icon' => 'ri-wallet-3-line',
            'href' => route('finance.index', ['tab' => 'transactions']),
            'active' => false,
        ],
        [
            'label' => 'Saques',
            'description' => 'Solicitações e repasses locais',
            'icon' => 'ri-bank-card-line',
            'href' => route('finance.index', ['tab' => 'withdrawals']),
            'active' => false,
        ],
        [
            'label' => 'Relatórios',
            'description' => 'Tendências de faturamento',
            'icon' => 'ri-file-chart-line',
            'href' => route('finance.index', ['tab' => 'reports']),
            'active' => false,
        ],
    ];
@endphp

<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Financeiro</h1>
            <div class="prime-clients-counters">
                <span class="prime-clients-counter prime-clients-counter--delivered">
                    <i class="ri-money-dollar-circle-fill"></i>
                    {{ $money($availableBalance) }} disponível
                </span>
                <span class="prime-clients-counter prime-clients-counter--pending">
                    <i class="ri-time-fill"></i>
                    {{ $money($pendingBalance) }} pendente
                </span>
            </div>
        </div>
        <div class="prime-clients-toolbar__right">
            <a href="{{ route('finance.index', ['tab' => 'withdrawals']) }}" class="prime-btn-success">
                <i class="ri-rocket-line"></i> Saque Turbo
            </a>
            @can('create payments')
                <a href="{{ route('invoices.create') }}" class="prime-btn-primary">
                    <i class="ri-add-line"></i> Nova venda
                </a>
            @endcan
        </div>
    </div>

    <p class="prime-page-sub mb-0">Saldo, transações e relatórios da {{ config('brand.name', 'MGTEAM FITNESS & HEALTH') }}.</p>

    <div class="prime-finance-balance-grid">
        <div class="prime-finance-balance-card prime-finance-balance-card--available">
            <div class="prime-finance-balance-card__top">
                <div>
                    <div class="prime-panel-label">Saldo disponível</div>
                    <div class="prime-finance-balance-value prime-money-value">{{ $money($availableBalance) }}</div>
                </div>
                <span class="prime-finance-balance-icon"><i class="ri-wallet-3-line"></i></span>
            </div>
            <p>Valor recebido em pagamentos registrados no banco local.</p>
            <a href="{{ route('finance.index', ['tab' => 'withdrawals']) }}" class="prime-btn-success prime-btn-success--wide">
                <i class="ri-rocket-line"></i> Saque Turbo
            </a>
        </div>

        <div class="prime-finance-balance-card">
            <div class="prime-finance-balance-card__top">
                <div>
                    <div class="prime-panel-label">Saldo pendente</div>
                    <div class="prime-finance-balance-value prime-money-value">{{ $money($pendingBalance) }}</div>
                </div>
                <span class="prime-finance-balance-icon prime-finance-balance-icon--pending"><i class="ri-time-line"></i></span>
            </div>
            <p>Faturas locais ainda não pagas ou parcialmente pagas.</p>
            <a href="{{ route('finance.index', ['tab' => 'transactions', 'status' => 'unpaid']) }}" class="prime-metric-link">Ver faturas pendentes</a>
        </div>
    </div>

    <div class="prime-finance-card-grid">
        @foreach($financeCards as $card)
            <a href="{{ $card['href'] }}" class="prime-finance-action-card @if($card['active']) is-active @endif">
                <span class="prime-finance-action-card__icon"><i class="{{ $card['icon'] }}"></i></span>
                <span class="prime-finance-action-card__body">
                    <strong>{{ $card['label'] }}</strong>
                    <small>{{ $card['description'] }}</small>
                </span>
                <i class="ri-arrow-right-s-line"></i>
            </a>
        @endforeach
    </div>

    <div class="row g-2">
        <div class="col-md-4">
            <div class="prime-panel prime-panel--compact h-100">
                <div class="prime-panel-label">Receita do mês</div>
                <div class="prime-panel-value prime-panel-value--sm prime-money-value">{{ $money($monthRevenue) }}</div>
                <p class="prime-panel-hint mb-0">{{ $monthTransactions }} transações</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="prime-panel prime-panel--compact h-100">
                <div class="prime-panel-label">Meta mensal</div>
                <div class="prime-panel-value prime-panel-value--sm prime-money-value">R$ 10.000,00</div>
                @php $pct = min(100, ($monthRevenue / 10000) * 100); @endphp
                <div class="prime-goal-track mt-2"><div class="prime-goal-fill" style="width: {{ $pct }}%"></div></div>
                <p class="prime-panel-hint mb-0">{{ number_format($pct, 0) }}% atingido</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="prime-panel prime-panel--compact h-100">
                <div class="prime-panel-label">Pagamentos digitais</div>
                <div class="prime-panel-value prime-panel-value--sm">{{ config('brand.pay', 'MGTEAM Pay') }}</div>
                <p class="prime-panel-hint mb-0">Conta local ativa para conciliação.</p>
            </div>
        </div>
    </div>

    <div class="prime-panel prime-panel--compact prime-finance-help">
        <ul class="nav prime-help-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#financeHelpCenter" type="button" role="tab">Central de ajuda</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#financeFees" type="button" role="tab">Taxas e prazos</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#financeBank" type="button" role="tab">Dados bancários</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#financeIdentity" type="button" role="tab">Identidade</button>
            </li>
        </ul>
        <div class="tab-content prime-help-tabs-content">
            <div class="tab-pane fade show active" id="financeHelpCenter" role="tabpanel">
                <div class="prime-help-row">
                    <span>Como acompanhar meus recebíveis?</span>
                    <a href="{{ route('finance.index', ['tab' => 'transactions']) }}">Ver transações</a>
                </div>
                <div class="prime-help-row">
                    <span>Onde encontro relatórios financeiros?</span>
                    <a href="{{ route('finance.index', ['tab' => 'reports']) }}">Abrir relatórios</a>
                </div>
            </div>
            <div class="tab-pane fade" id="financeFees" role="tabpanel">
                <div class="prime-help-row">
                    <span>Saque padrão</span>
                    <strong>Até 1 dia útil</strong>
                </div>
                <div class="prime-help-row">
                    <span>Simulação de taxa local</span>
                    <strong>1% ou mínimo de R$ 1,99</strong>
                </div>
            </div>
            <div class="tab-pane fade" id="financeBank" role="tabpanel">
                <div class="prime-help-row">
                    <span>Conta de destino</span>
                    <span class="prime-chip">Configuração local</span>
                </div>
                <div class="prime-help-row">
                    <span>Alterar dados bancários</span>
                    <a href="{{ route('settings.index') }}">Abrir configurações</a>
                </div>
            </div>
            <div class="tab-pane fade" id="financeIdentity" role="tabpanel">
                <div class="prime-help-row">
                    <span>Status da identidade</span>
                    <span class="prime-chip prime-chip--success">Ambiente local</span>
                </div>
                <div class="prime-help-row">
                    <span>Responsável pela conta</span>
                    <strong>{{ auth()->user()->name ?? config('brand.short', 'MGTEAM') }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="prime-panel prime-panel--compact">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="prime-section-title h6 mb-0">Últimas transações</h2>
            <a href="{{ route('finance.index', ['tab' => 'transactions']) }}" class="prime-btn-ghost prime-btn-ghost--sm">Ver todas</a>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle prime-table-compact">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Cliente</th>
                        <th>Fatura</th>
                        <th class="text-end">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentPayments as $payment)
                    <tr>
                        <td>{{ optional($payment->payment_date)->format('d/m/Y') ?? '—' }}</td>
                        <td>{{ $payment->invoice?->member?->name ?? '—' }}</td>
                        <td>
                            @if($payment->invoice)
                                <a href="{{ route('invoices.show', $payment->invoice) }}">{{ $payment->invoice->invoice_number }}</a>
                            @else — @endif
                        </td>
                        <td class="text-end text-success">{{ $money($payment->amount) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4">
                            <div class="prime-empty-state prime-empty-state--compact">
                                <i class="ri-wallet-3-line"></i>
                                <p>Nenhum pagamento registrado.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
