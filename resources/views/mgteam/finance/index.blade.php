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
    $invoiceStatusMeta = function ($invoice): array {
        if ($invoice->isOverdue()) {
            return ['label' => 'Atrasado', 'class' => 'mg-invoice-badge--overdue'];
        }

        return match ($invoice->status) {
            'paid' => ['label' => 'Pago', 'class' => 'mg-invoice-badge--paid'],
            'partially_paid' => ['label' => 'Pendente', 'class' => 'mg-invoice-badge--pending'],
            'unpaid' => ['label' => 'Pendente', 'class' => 'mg-invoice-badge--pending'],
            'cancelled' => ['label' => 'Cancelado', 'class' => 'mg-invoice-badge--muted'],
            default => ['label' => ucfirst((string) $invoice->status), 'class' => 'mg-invoice-badge--muted'],
        };
    };
@endphp

@push('styles')
    <style>
        .mg-finance-page {
            display: grid;
            gap: 0.85rem;
        }

        .mg-invoice-board {
            overflow: hidden;
            border: 1px solid #D8E0EA;
            border-radius: 0.86rem;
            background: #FFFFFF;
            box-shadow: 0 8px 22px rgba(23, 37, 56, 0.04);
        }

        .mg-invoice-row {
            display: grid;
            grid-template-columns: minmax(9rem, 0.8fr) minmax(12rem, 1.2fr) 7rem 8.5rem 8.5rem 8rem 9rem;
            gap: 0.55rem;
            align-items: center;
            min-height: 3.18rem;
            padding: 0.48rem 0.72rem;
            border-bottom: 1px solid #EDF1F6;
        }

        .mg-invoice-row:last-child {
            border-bottom: 0;
        }

        .mg-invoice-row--header {
            min-height: 2.35rem;
            background: #F6F8FB;
            color: #7A899F;
            font-size: 0.68rem;
            font-weight: 920;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .mg-invoice-number {
            color: #101929;
            font-size: 0.82rem;
            font-weight: 920;
            text-decoration: none;
        }

        .mg-invoice-client {
            overflow: hidden;
            color: #23324A;
            font-size: 0.8rem;
            font-weight: 820;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .mg-invoice-muted {
            color: #7F8DA3;
            font-size: 0.72rem;
            font-weight: 720;
        }

        .mg-invoice-money {
            color: #18263D;
            font-size: 0.8rem;
            font-weight: 900;
            text-align: right;
        }

        .mg-invoice-badge {
            display: inline-flex;
            width: fit-content;
            min-height: 1.45rem;
            align-items: center;
            padding: 0 0.52rem;
            border-radius: 999px;
            font-size: 0.68rem;
            font-weight: 920;
        }

        .mg-invoice-badge--paid {
            background: #E8F8EF;
            color: #168A46;
        }

        .mg-invoice-badge--pending {
            background: #FFF5D8;
            color: #9B6A00;
        }

        .mg-invoice-badge--overdue {
            background: #FEECEB;
            color: #C7362F;
        }

        .mg-invoice-badge--muted {
            background: #EEF2F7;
            color: #66758B;
        }

        .mg-invoice-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.35rem;
        }

        .mg-invoice-mini-btn {
            display: inline-flex;
            min-height: 1.88rem;
            align-items: center;
            justify-content: center;
            padding: 0 0.54rem;
            border: 1px solid #DCE5EF;
            border-radius: 0.58rem;
            background: #FFFFFF;
            color: #30425D;
            font-size: 0.7rem;
            font-weight: 880;
            text-decoration: none;
        }

        .mg-invoice-mini-btn--success {
            border-color: rgba(22, 138, 70, 0.26);
            background: #E8F8EF;
            color: #168A46;
        }

        @media (max-width: 1199.98px) {
            .mg-invoice-row {
                grid-template-columns: minmax(10rem, 1fr) minmax(10rem, 1fr) repeat(2, 7rem);
            }

            .mg-invoice-row > :nth-child(5),
            .mg-invoice-row > :nth-child(6),
            .mg-invoice-row > :nth-child(7) {
                display: none;
            }
        }

        @media (max-width: 767.98px) {
            .mg-invoice-row,
            .mg-invoice-row--header {
                grid-template-columns: 1fr;
            }

            .mg-invoice-row--header {
                display: none;
            }

            .mg-invoice-money,
            .mg-invoice-actions {
                justify-content: flex-start;
                text-align: left;
            }
        }
    </style>
@endpush

<div class="mg-clients-page mg-finance-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Financeiro</h1>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter mg-clients-counter--delivered">
                    <i class="ri-money-dollar-circle-fill"></i>
                    {{ $money($availableBalance) }} disponível
                </span>
                <span class="mg-clients-counter mg-clients-counter--pending">
                    <i class="ri-time-fill"></i>
                    {{ $money($pendingBalance) }} pendente
                </span>
            </div>
        </div>
        <div class="mg-clients-toolbar__right">
            <a href="{{ route('finance.index', ['tab' => 'withdrawals']) }}" class="mg-btn-success">
                <i class="ri-rocket-line"></i> Saque Turbo
            </a>
            @can('create payments')
                <a href="{{ route('invoices.create') }}" class="mg-btn-primary">
                    <i class="ri-add-line"></i> Nova venda
                </a>
            @endcan
        </div>
    </div>

    <p class="mg-page-sub mb-0">Saldo, transações e relatórios da {{ config('brand.name', 'MGTEAM FITNESS & HEALTH') }}.</p>

    <div class="mg-finance-balance-grid">
        <div class="mg-finance-balance-card mg-finance-balance-card--available">
            <div class="mg-finance-balance-card__top">
                <div>
                    <div class="mg-panel-label">Saldo disponível</div>
                    <div class="mg-finance-balance-value mg-money-value">{{ $money($availableBalance) }}</div>
                </div>
                <span class="mg-finance-balance-icon"><i class="ri-wallet-3-line"></i></span>
            </div>
            <p>Valor recebido em pagamentos registrados no banco local.</p>
            <a href="{{ route('finance.index', ['tab' => 'withdrawals']) }}" class="mg-btn-success mg-btn-success--wide">
                <i class="ri-rocket-line"></i> Saque Turbo
            </a>
        </div>

        <div class="mg-finance-balance-card">
            <div class="mg-finance-balance-card__top">
                <div>
                    <div class="mg-panel-label">Saldo pendente</div>
                    <div class="mg-finance-balance-value mg-money-value">{{ $money($pendingBalance) }}</div>
                </div>
                <span class="mg-finance-balance-icon mg-finance-balance-icon--pending"><i class="ri-time-line"></i></span>
            </div>
            <p>Faturas locais ainda não pagas ou parcialmente pagas.</p>
            <a href="{{ route('finance.index', ['tab' => 'transactions', 'status' => 'unpaid']) }}" class="mg-metric-link">Ver faturas pendentes</a>
        </div>
    </div>

    <div class="mg-finance-card-grid">
        @foreach($financeCards as $card)
            <a href="{{ $card['href'] }}" class="mg-finance-action-card @if($card['active']) is-active @endif">
                <span class="mg-finance-action-card__icon"><i class="{{ $card['icon'] }}"></i></span>
                <span class="mg-finance-action-card__body">
                    <strong>{{ $card['label'] }}</strong>
                    <small>{{ $card['description'] }}</small>
                </span>
                <i class="ri-arrow-right-s-line"></i>
            </a>
        @endforeach
    </div>

    <div class="row g-2">
        <div class="col-md-4">
            <div class="mg-panel mg-panel--compact h-100">
                <div class="mg-panel-label">Receita do mês</div>
                <div class="mg-panel-value mg-panel-value--sm mg-money-value">{{ $money($monthRevenue) }}</div>
                <p class="mg-panel-hint mb-0">{{ $monthTransactions }} transações</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="mg-panel mg-panel--compact h-100">
                <div class="mg-panel-label">Meta mensal</div>
                <div class="mg-panel-value mg-panel-value--sm mg-money-value">R$ 10.000,00</div>
                @php $pct = min(100, ($monthRevenue / 10000) * 100); @endphp
                <div class="mg-goal-track mt-2"><div class="mg-goal-fill" style="width: {{ $pct }}%"></div></div>
                <p class="mg-panel-hint mb-0">{{ number_format($pct, 0) }}% atingido</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="mg-panel mg-panel--compact h-100">
                <div class="mg-panel-label">Pagamentos digitais</div>
                <div class="mg-panel-value mg-panel-value--sm">{{ config('brand.pay', 'MGTEAM Pay') }}</div>
                <p class="mg-panel-hint mb-0">Conta local ativa para conciliação.</p>
            </div>
        </div>
    </div>

    <div class="mg-panel mg-panel--compact mg-finance-help">
        <ul class="nav mg-help-tabs" role="tablist">
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
        <div class="tab-content mg-help-tabs-content">
            <div class="tab-pane fade show active" id="financeHelpCenter" role="tabpanel">
                <div class="mg-help-row">
                    <span>Como acompanhar meus recebíveis?</span>
                    <a href="{{ route('finance.index', ['tab' => 'transactions']) }}">Ver transações</a>
                </div>
                <div class="mg-help-row">
                    <span>Onde encontro relatórios financeiros?</span>
                    <a href="{{ route('finance.index', ['tab' => 'reports']) }}">Abrir relatórios</a>
                </div>
            </div>
            <div class="tab-pane fade" id="financeFees" role="tabpanel">
                <div class="mg-help-row">
                    <span>Saque padrão</span>
                    <strong>Até 1 dia útil</strong>
                </div>
                <div class="mg-help-row">
                    <span>Simulação de taxa local</span>
                    <strong>1% ou mínimo de R$ 1,99</strong>
                </div>
            </div>
            <div class="tab-pane fade" id="financeBank" role="tabpanel">
                <div class="mg-help-row">
                    <span>Conta de destino</span>
                    <span class="mg-chip">Configuração local</span>
                </div>
                <div class="mg-help-row">
                    <span>Alterar dados bancários</span>
                    <a href="{{ route('settings.index') }}">Abrir configurações</a>
                </div>
            </div>
            <div class="tab-pane fade" id="financeIdentity" role="tabpanel">
                <div class="mg-help-row">
                    <span>Status da identidade</span>
                    <span class="mg-chip mg-chip--success">Ambiente local</span>
                </div>
                <div class="mg-help-row">
                    <span>Responsável pela conta</span>
                    <strong>{{ auth()->user()->name ?? config('brand.short', 'MGTEAM') }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="mg-panel mg-panel--compact">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="mg-section-title h6 mb-0">Faturas recentes</h2>
            <a href="{{ route('finance.index', ['tab' => 'transactions']) }}" class="mg-btn-ghost mg-btn-ghost--sm">Ver todas</a>
        </div>
        <div class="mg-invoice-board">
            <div class="mg-invoice-row mg-invoice-row--header">
                <span>Fatura</span>
                <span>Cliente</span>
                <span>Status</span>
                <span class="text-end">Total</span>
                <span class="text-end">Pago</span>
                <span>Vencimento</span>
                <span class="text-end">Ações</span>
            </div>
            @forelse($recentInvoices as $invoice)
                @php $statusMeta = $invoiceStatusMeta($invoice); @endphp
                <div class="mg-invoice-row">
                    <a href="{{ route('invoices.show', $invoice) }}" class="mg-invoice-number">{{ $invoice->invoice_number }}</a>
                    <div>
                        <div class="mg-invoice-client">{{ $invoice->member?->name ?? 'Cliente não informado' }}</div>
                        <div class="mg-invoice-muted">{{ optional($invoice->invoice_date)->format('d/m/Y') ?? 'Sem emissão' }}</div>
                    </div>
                    <span class="mg-invoice-badge {{ $statusMeta['class'] }}">{{ $statusMeta['label'] }}</span>
                    <div class="mg-invoice-money">{{ $money($invoice->total_amount) }}</div>
                    <div class="mg-invoice-money">{{ $money($invoice->paid_amount) }}</div>
                    <div class="mg-invoice-muted">{{ optional($invoice->due_date)->format('d/m/Y') ?? '—' }}</div>
                    <div class="mg-invoice-actions">
                        <a href="{{ route('invoices.show', $invoice) }}" class="mg-invoice-mini-btn">Ver</a>
                        @if(! $invoice->isPaid() && $invoice->remaining_balance > 0)
                            <form method="POST" action="{{ route('invoices.addPayment', $invoice) }}" onsubmit="return confirm('Dar baixa integral nesta fatura?')">
                                @csrf
                                <input type="hidden" name="amount" value="{{ number_format($invoice->remaining_balance, 2, '.', '') }}">
                                <input type="hidden" name="payment_date" value="{{ now()->toDateString() }}">
                                <input type="hidden" name="payment_method" value="bank_transfer">
                                <input type="hidden" name="notes" value="Baixa manual registrada pelo dashboard financeiro.">
                                <button type="submit" class="mg-invoice-mini-btn mg-invoice-mini-btn--success">Dar baixa</button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="mg-empty-state mg-empty-state--compact">
                    <i class="ri-file-list-3-line"></i>
                    <p>Nenhuma fatura emitida.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="mg-panel mg-panel--compact">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="mg-section-title h6 mb-0">Últimas transações</h2>
            <a href="{{ route('finance.index', ['tab' => 'transactions']) }}" class="mg-btn-ghost mg-btn-ghost--sm">Ver todas</a>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle mg-table-compact">
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
                            <div class="mg-empty-state mg-empty-state--compact">
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
