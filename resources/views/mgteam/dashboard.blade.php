@extends('layouts.master')

@section('title', 'Resumo')

@section('content')
@php
    $greeting = match (true) {
        now()->hour < 12 => 'Bom dia',
        now()->hour < 18 => 'Boa tarde',
        default => 'Boa noite',
    };
    $firstName = explode(' ', auth()->user()->name)[0];
    $daysInMonth = now()->daysInMonth;
    $dayOfMonth = now()->day;
    $projection = $dayOfMonth > 0 ? ($stats['revenue_month'] / $dayOfMonth) * $daysInMonth : 0;
    $selectedDay = request('day', $upcomingDays->first()['key'] ?? now()->format('Y-m-d'));
    $selectedDayEvents = $upcomingEvents->get($selectedDay, collect());
    $selectedDayLabel = \Carbon\Carbon::parse($selectedDay)->locale('pt_BR');
    $genderTotal = max(1, $stats['clients_male'] + $stats['clients_female']);
    $malePct = round(($stats['clients_male'] / $genderTotal) * 100);
    $femalePct = 100 - $malePct;
    $formatDelta = function (float $value): string {
        $sign = $value > 0 ? '+' : '';
        return $sign . number_format($value, 1, ',', '.') . '%';
    };
    $deltaClass = fn (float $v) => $v >= 0 ? 'mg-delta--up' : 'mg-delta--down';
    $maxTrend = max(1, collect($dailyTrend)->max('cumulative'));
@endphp

@push('styles')
<style>
    .mg-greeting {
        margin: 0 0 2.18rem;
        padding-top: 0.15rem;
    }

    .mg-greeting h1 {
        margin-bottom: 0.45rem;
        color: #FFFFFF;
        font-size: clamp(2rem, 2.4vw, 2.62rem);
        font-weight: 950;
        letter-spacing: -0.055em;
    }

    .mg-greeting p,
    .mg-live-badge {
        color: #A2AEC4;
        font-size: 1rem;
        font-weight: 650;
    }

    .mg-live-badge::before {
        background: #14C97A;
        box-shadow: 0 0 0 4px rgba(20, 201, 122, 0.08);
    }

    .mg-section-head {
        gap: 0.55rem;
        margin-bottom: 1.05rem !important;
    }

    .mg-section-pill {
        height: 1.72rem;
        padding: 0 0.78rem;
        border: 1px solid rgba(59, 149, 255, 0.34) !important;
        border-radius: 999px;
        background: rgba(59, 149, 255, 0.12) !important;
        color: #BBD7FF !important;
        font-size: 0.72rem;
        font-weight: 950;
        letter-spacing: 0.1em;
    }

    .mg-section-title {
        color: #FFFFFF;
        font-size: 1.24rem;
        font-weight: 920;
        letter-spacing: -0.03em;
    }

    .mg-billing-card {
        margin-bottom: 2.45rem !important;
        padding: 1.42rem;
        border: 1px solid rgba(40, 94, 168, 0.58) !important;
        border-radius: 1.45rem;
        background: linear-gradient(180deg, rgba(10, 25, 52, 0.92), rgba(7, 17, 35, 0.98)) !important;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.035) !important;
    }

    .mg-billing-grid {
        display: grid;
        grid-template-columns: minmax(24rem, 1fr) minmax(34rem, 0.95fr);
        gap: 2.2rem;
    }

    .mg-billing-hero {
        min-height: 13.4rem;
        padding: 1.25rem 1.45rem;
        border: 0 !important;
        border-radius: 1rem;
        background: transparent !important;
    }

    .mg-billing-hero .mg-panel-label,
    .mg-billing-mini .mg-panel-label {
        color: #8F9DB3 !important;
        font-size: 0.76rem;
        font-weight: 950;
        letter-spacing: 0.09em;
    }

    .mg-billing-hero .mg-panel-value {
        color: #FFFFFF;
        font-size: clamp(3rem, 4vw, 4.3rem);
        line-height: 0.95;
        font-weight: 950;
        letter-spacing: -0.07em;
    }

    .mg-billing-compare {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
    }

    .mg-billing-mini {
        min-height: 11.2rem;
        padding: 1.1rem 1.15rem;
        border: 1px solid rgba(87, 111, 148, 0.34) !important;
        border-radius: 0.98rem;
        background: #172235 !important;
    }

    .mg-billing-mini .mg-panel-value--sm {
        margin-top: 1.1rem;
        color: #FFFFFF;
        font-size: 1.82rem;
        font-weight: 950;
        letter-spacing: -0.045em;
    }

    .mg-delta {
        display: inline-flex;
        align-items: center;
        min-height: 1.52rem;
        margin-top: 0.4rem;
        padding: 0 0.62rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 950;
    }

    .mg-delta--up {
        background: rgba(20, 201, 122, 0.16);
        color: #73E7AE;
    }

    .mg-delta--down {
        background: rgba(244, 111, 104, 0.18);
        color: #FF8C85;
    }

    .mg-billing-footer {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(87, 111, 148, 0.28);
    }

    .mg-metric-card {
        min-height: 10.8rem;
        padding: 1.18rem 1.28rem;
        border-radius: 1.25rem;
        background: #111A2B !important;
    }

    .mg-metric-top span {
        color: #D6DEEC;
        font-size: 1.02rem;
        font-weight: 850;
        line-height: 1.25;
    }

    .mg-metric-icon {
        width: 2.72rem;
        height: 2.72rem;
        border-radius: 0.78rem;
        background: #0C356B !important;
        color: #9ECFFF !important;
        font-size: 1.18rem;
    }

    .mg-metric-icon--orange {
        background: rgba(246, 178, 61, 0.16) !important;
        color: #F6C86F !important;
    }

    .mg-metric-icon--red {
        background: rgba(244, 111, 104, 0.18) !important;
        color: #FF9A95 !important;
    }

    .mg-metric-value {
        color: #FFFFFF;
        font-size: 2.45rem;
        font-weight: 950;
    }

    .mg-metric-link {
        color: #BBD7FF;
        font-size: 0.88rem;
        font-weight: 820;
    }

    .mg-panel {
        border-radius: 1.25rem;
        background: #111A2B !important;
    }

    .mg-day-pills {
        gap: 0.45rem;
        overflow-x: auto;
        padding: 0.25rem 0 0.45rem;
    }

    .mg-day-pill {
        min-width: 4.25rem;
        min-height: 4.05rem;
        border: 1px solid rgba(87, 111, 148, 0.34);
        border-radius: 0.82rem;
        background: #081422 !important;
        color: #CBD5E6 !important;
    }

    .mg-day-pill.is-active {
        background: #3B95FF !important;
        border-color: #3B95FF !important;
        color: #FFFFFF !important;
        box-shadow: none !important;
    }

    .mg-day-pill-count {
        background: #61AAFF !important;
        color: #FFFFFF !important;
    }

    .mg-active-clients-value {
        color: #FFFFFF;
        font-size: 3.65rem;
        font-weight: 950;
        letter-spacing: -0.07em;
    }

    .mg-gender-bar-fill--male {
        background: #3B95FF !important;
    }

    .mg-gender-bar-fill--female {
        background: #9B6CF6 !important;
    }

    @media (max-width: 1200px) {
        .mg-billing-grid,
        .mg-billing-compare {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

<div class="mg-greeting">
    <div>
        <h1>{{ $greeting }}, {{ $firstName }}! 👋</h1>
        <p>Acompanhe o desempenho do seu negócio</p>
    </div>
    <div class="mg-live-badge">ao vivo · {{ now()->locale('pt_BR')->translatedFormat('l, j \d\e F') }}</div>
</div>

<div class="mg-section-head mb-3">
    <span class="mg-section-pill">Visão geral</span>
    <h2 class="mg-section-title mb-0">Faturamento do mês</h2>
</div>

<div class="mg-billing-card mb-4" id="mgRevenueSection">
    <div class="mg-billing-grid">
        <div class="mg-billing-hero">
            <div class="mg-panel-label">{{ strtoupper(now()->translatedFormat('F')) }} · ATÉ HOJE <i class="ri-information-line"></i></div>
            <div class="mg-panel-value mg-money-value">R$ {{ number_format($stats['revenue_month'], 0, ',', '.') }}</div>
            <p class="mg-panel-hint">Sem período anterior pra comparar</p>
            <div class="mg-legend-row my-2">
                <span><i class="mg-dot mg-dot--blue"></i> {{ config('brand.pay', 'MGTEAM Pay') }}</span>
                <span><i class="mg-dot mg-dot--orange"></i> Manual</span>
            </div>
            <p class="mg-panel-hint mb-1">No ritmo atual deve fechar em <strong class="mg-money-value">R$ {{ number_format($projection, 0, ',', '.') }}</strong></p>
            <p class="mg-panel-hint mb-0">Média dos últimos 3 meses: <strong class="mg-money-value">R$ {{ number_format($stats['avg_3_months'], 0, ',', '.') }}</strong></p>
        </div>
        <div class="mg-billing-compare mg-revenue-compare-col">
            <div class="mg-billing-mini">
                <div class="d-flex justify-content-between"><span class="mg-panel-label mb-0">Hoje</span><i class="ri-calendar-line text-muted"></i></div>
                <div class="mg-panel-value mg-panel-value--sm mg-money-value">R$ {{ number_format($stats['revenue_today'], 2, ',', '.') }}</div>
                <span class="mg-delta {{ $deltaClass($stats['delta_today']) }}">{{ $formatDelta($stats['delta_today']) }}</span>
                <div class="mg-mini-sub">vs ontem</div>
            </div>
            <div class="mg-billing-mini">
                <div class="d-flex justify-content-between"><span class="mg-panel-label mb-0">Esta semana</span><i class="ri-calendar-line text-muted"></i></div>
                <div class="mg-panel-value mg-panel-value--sm mg-money-value">R$ {{ number_format($stats['revenue_week'], 2, ',', '.') }}</div>
                <span class="mg-delta {{ $deltaClass($stats['delta_week']) }}">{{ $formatDelta($stats['delta_week']) }}</span>
                <div class="mg-mini-sub">vs semana passada</div>
            </div>
            <div class="mg-billing-mini">
                <div class="d-flex justify-content-between"><span class="mg-panel-label mb-0">Este ano</span><i class="ri-line-chart-line text-muted"></i></div>
                <div class="mg-panel-value mg-panel-value--sm mg-money-value">R$ {{ number_format($stats['revenue_year'], 2, ',', '.') }}</div>
                <div class="mg-mini-sub">Sem período anterior</div>
            </div>
        </div>
    </div>
    <div class="mg-billing-footer">
        <div></div>
        <div class="mg-billing-actions">
            <button type="button" class="mg-ghost-link" id="mgHideValues"><i class="ri-eye-off-line"></i> Ocultar valores</button>
            <button type="button" class="mg-ghost-link" id="mgHideDetail"><i class="ri-layout-row-line"></i> Ocultar detalhamento</button>
        </div>
    </div>
</div>

<div class="mg-section-head mb-3">
    <span class="mg-section-pill">Operacional</span>
    <h2 class="mg-section-title mb-0">Atendimentos & agenda</h2>
</div>

<div class="row g-3 mb-4">
    @foreach([
        ['label' => 'Atendimentos pendentes', 'value' => $stats['pending_attendances'], 'icon' => 'ri-time-line', 'tone' => 'blue', 'route' => 'members.attendances'],
        ['label' => 'Feedbacks pendentes', 'value' => $stats['pending_feedbacks'], 'icon' => 'ri-chat-1-line', 'tone' => 'orange', 'route' => 'feedbacks.index'],
        ['label' => 'Conversas não lidas', 'value' => $stats['unread_messages'], 'icon' => 'ri-message-3-line', 'tone' => 'orange', 'route' => 'messages.index'],
        ['label' => 'Faturas em atraso', 'value' => $stats['overdue_invoices'] ?? 0, 'icon' => 'ri-error-warning-line', 'tone' => 'red', 'route' => 'finance.index'],
        ['label' => 'Templates de treino', 'value' => $stats['library_templates'] ?? 0, 'icon' => 'ri-list-check-3', 'tone' => 'blue', 'route' => 'workout-templates.index'],
        ['label' => 'Cardio ativos', 'value' => $stats['active_cardio_plans'] ?? 0, 'icon' => 'ri-heart-pulse-line', 'tone' => 'orange', 'route' => 'members.index'],
    ] as $metric)
        <div class="col-xl col-md-4 col-6">
            <a href="{{ route($metric['route']) }}" class="mg-metric-card text-decoration-none">
                <div class="mg-metric-top">
                    <span>{{ $metric['label'] }}</span>
                    <i class="mg-metric-icon mg-metric-icon--{{ $metric['tone'] }} {{ $metric['icon'] }}"></i>
                </div>
                <div class="mg-metric-value">{{ $metric['value'] }}</div>
                <span class="mg-metric-link">ver detalhes ›</span>
            </a>
        </div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-7">
        <div class="mg-panel h-100">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                <div>
                    <h2 class="mg-section-title h5 mb-1">Próximos agendamentos</h2>
                    <span class="text-muted small">{{ $selectedDayEvents->count() }} compromisso(s) em {{ $selectedDayLabel->translatedFormat('D d/M') }}</span>
                </div>
            </div>
            <div class="mg-day-pills mb-3">
                @foreach($upcomingDays as $day)
                    <a href="?day={{ $day['key'] }}" class="mg-day-pill @if($selectedDay === $day['key']) is-active @endif">
                        <em class="mg-day-pill-count">{{ $day['count'] }}</em>
                        <strong>{{ ucfirst($day['date']->locale('pt_BR')->translatedFormat('D')) }}</strong>
                        <span>{{ $day['date']->format('d/M') }}</span>
                    </a>
                @endforeach
            </div>
            <div class="mg-agenda-list">
                @forelse($selectedDayEvents as $event)
                    <a href="{{ route('members.show', $event->member_id) }}" class="mg-agenda-item">
                        <span class="mg-agenda-name">{{ $event->member?->name ?? 'Sem cliente' }}</span>
                        <span class="mg-agenda-meta">{{ $event->title }} · {{ $event->start_time->format('H:i') }}</span>
                    </a>
                @empty
                    @forelse($pendingFeedbacksList->take(8) as $feedback)
                        <a href="{{ route('feedbacks.index') }}" class="mg-agenda-item">
                            <span class="mg-agenda-name">Feedback · {{ $feedback->member?->email ?? 'cliente' }}</span>
                        </a>
                    @empty
                        <div class="mg-empty-state mg-empty-state--compact">
                            <i class="ri-calendar-line"></i>
                            <p>Nenhum agendamento para este dia.</p>
                        </div>
                    @endforelse
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-xl-5">
        <div class="mg-panel h-100">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h2 class="mg-section-title h5 mb-0">Clientes ativos</h2>
                <i class="ri-group-line text-muted"></i>
            </div>
            <div class="mg-active-clients">
                <div class="mg-active-clients-value">{{ $stats['clients_active'] }}</div>
                <div class="mg-active-clients-label">em acompanhamento</div>
            </div>
            @if($stats['clients_male'] + $stats['clients_female'] > 0)
                <div class="mg-gender-bar mt-4">
                    <div class="mg-gender-bar-fill mg-gender-bar-fill--male" style="width: {{ $malePct }}%"></div>
                    <div class="mg-gender-bar-fill mg-gender-bar-fill--female" style="width: {{ $femalePct }}%"></div>
                </div>
                <div class="d-flex justify-content-between mt-2 small text-muted">
                    <span><i class="mg-dot mg-dot--blue"></i> Homens {{ $stats['clients_male'] }} · {{ $malePct }}%</span>
                    <span><i class="mg-dot mg-dot--purple"></i> Mulheres {{ $stats['clients_female'] }} · {{ $femalePct }}%</span>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="mg-section-head mb-3">
    <span class="mg-section-pill">Ações rápidas</span>
    <h2 class="mg-section-title mb-0">Listas do dia</h2>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-4">
        <div class="mg-panel h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mg-section-title h6 mb-0">Pagamentos pendentes</h3>
                <a href="{{ route('finance.index', ['tab' => 'transactions', 'status' => 'overdue']) }}" class="small">ver todos</a>
            </div>
            @forelse($pendingInvoices ?? [] as $invoice)
                <a href="{{ route('invoices.show', $invoice) }}" class="mg-agenda-item">
                    <span class="mg-agenda-name">{{ $invoice->member?->name ?? 'Cliente' }}</span>
                    <span class="mg-agenda-meta">R$ {{ number_format($invoice->total_amount - $invoice->paid_amount, 2, ',', '.') }} · vence {{ $invoice->due_date?->format('d/m') }}</span>
                </a>
            @empty
                <div class="mg-empty-state mg-empty-state--compact">
                    <i class="ri-checkbox-circle-line"></i>
                    <p>Nenhuma fatura em aberto.</p>
                </div>
            @endforelse
        </div>
    </div>
    <div class="col-xl-4">
        <div class="mg-panel h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mg-section-title h6 mb-0">Aniversariantes</h3>
                <a href="{{ route('members.index') }}" class="small">clientes</a>
            </div>
            @forelse($birthdays ?? [] as $member)
                <a href="{{ route('members.show', $member) }}" class="mg-agenda-item">
                    <span class="mg-agenda-name">{{ $member->name }}</span>
                    <span class="mg-agenda-meta">{{ $member->date_of_birth?->format('d/m') }}</span>
                </a>
            @empty
                <div class="mg-empty-state mg-empty-state--compact">
                    <i class="ri-cake-2-line"></i>
                    <p>Nenhum aniversário nos próximos 14 dias.</p>
                </div>
            @endforelse
        </div>
    </div>
    <div class="col-xl-4">
        <div class="mg-panel h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mg-section-title h6 mb-0">Treinos / renovações</h3>
                <a href="{{ route('members.renewals') }}" class="small">ver</a>
            </div>
            @forelse($upcomingRenewals ?? [] as $member)
                <a href="{{ route('members.show', $member) }}" class="mg-agenda-item">
                    <span class="mg-agenda-name">{{ $member->name }}</span>
                    <span class="mg-agenda-meta">Renova {{ $member->membership_end_date?->format('d/m') }}</span>
                </a>
            @empty
                @forelse($expiringWorkouts ?? [] as $workout)
                    <a href="{{ route('members.show', $workout->member_id) }}" class="mg-agenda-item">
                        <span class="mg-agenda-name">{{ $workout->member?->name ?? 'Cliente' }}</span>
                        <span class="mg-agenda-meta">{{ $workout->name }} · {{ $workout->workout_date?->format('d/m') }}</span>
                    </a>
                @empty
                    <div class="mg-empty-state mg-empty-state--compact">
                        <i class="ri-run-line"></i>
                        <p>Nenhuma renovação ou treino vencendo.</p>
                    </div>
                @endforelse
            @endforelse
        </div>
    </div>
</div>

<div class="mg-section-head mb-3">
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-2 w-100">
        <div>
            <span class="mg-section-pill">Financeiro</span>
            <h2 class="mg-section-title mb-0 mt-2">Vendas & receita</h2>
        </div>
        <div class="mg-billing-actions">
            <button type="button" class="mg-ghost-link" id="mgHideValuesSales"><i class="ri-eye-off-line"></i> Ocultar valores</button>
            <button type="button" class="mg-ghost-link" id="mgHideDetailSales"><i class="ri-layout-row-line"></i> Ocultar detalhamento</button>
        </div>
    </div>
</div>

<div class="row g-3 mb-3" id="mgSalesDetail">
    @foreach([
        ['label' => 'Resumo diário', 'value' => $stats['revenue_today'], 'delta' => $stats['delta_today'], 'delta_label' => 'vs ' . today()->subDay()->format('d/m/Y')],
        ['label' => 'Vendas por período', 'value' => $stats['revenue_week'], 'delta' => $stats['delta_week'], 'delta_label' => 'vs semana passada'],
        ['label' => 'Vendas mensais', 'value' => $stats['revenue_month'], 'delta' => $stats['delta_month'], 'delta_label' => 'vs mês anterior'],
        ['label' => 'Ticket médio', 'value' => $stats['ticket_avg'], 'hint' => 'Por transação', 'delta' => $stats['delta_week'], 'delta_label' => 'vs semana passada'],
    ] as $card)
        <div class="col-xl-3 col-md-6">
            <div class="mg-panel h-100">
                <div class="mg-panel-label">{{ $card['label'] }}</div>
                <div class="mg-panel-value mg-panel-value--sm mg-money-value">R$ {{ number_format($card['value'], 2, ',', '.') }}</div>
                @if(!empty($card['hint']))
                    <p class="mg-panel-hint mb-1">{{ $card['hint'] }}</p>
                @endif
                <span class="mg-delta {{ $deltaClass($card['delta']) }}">{{ $formatDelta($card['delta']) }} {{ $card['delta_label'] }}</span>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="mg-panel h-100">
            <div class="mg-panel-label">Expectativa de renovação</div>
            <div class="mg-panel-value mg-panel-value--sm mg-money-value">R$ {{ number_format($stats['renewal_expectation_amount'], 2, ',', '.') }}</div>
            <p class="mg-panel-hint mb-0">{{ $stats['renewal_expectation_clients'] }} clientes</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mg-panel h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div class="mg-panel-label">Meta mensal</div>
                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" data-bs-toggle="modal" data-bs-target="#mgGoalModal">Editar</button>
            </div>
            <div class="mg-panel-value mg-panel-value--sm mg-money-value">R$ {{ number_format($stats['monthly_goal'], 2, ',', '.') }}</div>
            <p class="mg-panel-hint mb-2">Faltam: <span class="mg-money-value">R$ {{ number_format($stats['goal_remaining'], 2, ',', '.') }}</span></p>
            <div class="mg-goal-track"><div class="mg-goal-fill" style="width: {{ $stats['goal_progress'] }}%"></div></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mg-panel h-100">
            <div class="mg-panel-label">Resumo do período</div>
            <p class="small text-muted mb-2">{{ $startOfMonth->format('d/m/Y') }} — {{ now()->format('d/m/Y') }}</p>
            <div class="d-flex justify-content-between small py-1"><span>Receita total</span><strong class="mg-money-value">R$ {{ number_format($stats['revenue_month'], 2, ',', '.') }}</strong></div>
            <div class="d-flex justify-content-between small py-1"><span>Total de transações</span><strong>{{ $stats['transactions_month'] }}</strong></div>
            <div class="d-flex justify-content-between small py-1"><span>Ticket médio</span><strong class="mg-money-value">R$ {{ number_format($stats['ticket_avg'], 2, ',', '.') }}</strong></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="mg-panel h-100">
            <h3 class="mg-section-title h6">Novas vendas</h3>
            <div class="row g-2 text-center">
                <div class="col-4"><div class="mg-stat-mini"><span>Quantidade</span><strong>{{ $stats['new_sales_count'] }}</strong></div></div>
                <div class="col-4"><div class="mg-stat-mini"><span>Valor total</span><strong class="mg-money-value">R$ {{ number_format($stats['new_sales_total'], 0, ',', '.') }}</strong></div></div>
                <div class="col-4"><div class="mg-stat-mini"><span>Ticket médio</span><strong class="mg-money-value">R$ {{ number_format($stats['new_sales_count'] > 0 ? $stats['new_sales_total'] / $stats['new_sales_count'] : 0, 2, ',', '.') }}</strong></div></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mg-panel h-100">
            <h3 class="mg-section-title h6">Renovações</h3>
            <div class="row g-2 text-center">
                <div class="col-4"><div class="mg-stat-mini"><span>Quantidade</span><strong>{{ $stats['renewal_sales_count'] }}</strong></div></div>
                <div class="col-4"><div class="mg-stat-mini"><span>Valor total</span><strong class="mg-money-value">R$ {{ number_format($stats['renewal_sales_total'], 0, ',', '.') }}</strong></div></div>
                <div class="col-4"><div class="mg-stat-mini"><span>Ticket médio</span><strong class="mg-money-value">R$ {{ number_format($stats['renewal_sales_count'] > 0 ? $stats['renewal_sales_total'] / $stats['renewal_sales_count'] : 0, 2, ',', '.') }}</strong></div></div>
            </div>
        </div>
    </div>
</div>

<div class="mg-section-head mb-3">
    <span class="mg-section-pill">Saúde</span>
    <h2 class="mg-section-title mb-0">Saúde & tendência</h2>
</div>

<div class="row g-3 mb-4">
    @foreach([
        ['label' => 'Taxa de renovação', 'value' => number_format($stats['renewal_rate'], 2, ',', '.') . '%', 'hint' => $stats['renewal_sales_count'] . ' renovaram no mês'],
        ['label' => 'Receita recorrente', 'value' => number_format($stats['recurring_pct'], 0) . '%', 'hint' => 'R$ ' . number_format($stats['renewal_sales_total'], 2, ',', '.') . ' do faturamento'],
        ['label' => 'Chargeback / reembolso', 'value' => number_format($stats['chargeback_pct'], 0) . '%', 'hint' => 'Reembolso: 0%'],
        ['label' => 'LTV médio', 'value' => 'R$ ' . number_format($stats['ltv_avg'], 2, ',', '.'), 'hint' => 'por cliente'],
    ] as $health)
        <div class="col-xl-3 col-md-6">
            <div class="mg-panel h-100">
                <div class="mg-panel-label">{{ $health['label'] }}</div>
                <div class="mg-panel-value mg-panel-value--sm">{{ $health['value'] }}</div>
                <p class="mg-panel-hint mb-0">{{ $health['hint'] }}</p>
            </div>
        </div>
    @endforeach
</div>

<div class="mg-panel mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mg-section-title h6 mb-1">Tendência diária — receita acumulada</h3>
            <p class="mg-panel-hint mb-0">R$ {{ number_format(collect($dailyTrend)->last()['cumulative'] ?? 0, 0, ',', '.') }} acumulado</p>
        </div>
        <div class="mg-chart-toggle" role="group">
            <button type="button" class="mg-chart-toggle-btn is-active">Receita</button>
            <button type="button" class="mg-chart-toggle-btn">Clientes</button>
        </div>
    </div>
    <div class="mg-trend-chart">
        @foreach($dailyTrend as $point)
            <div class="mg-trend-bar-wrap" title="{{ $point['label'] }}: R$ {{ number_format($point['cumulative'], 0, ',', '.') }}">
                <div class="mg-trend-bar" style="height: {{ max(4, ($point['cumulative'] / $maxTrend) * 100) }}%"></div>
                <span>{{ $point['label'] }}</span>
            </div>
        @endforeach
    </div>
</div>

<div class="modal fade" id="mgGoalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content mg-panel" style="height:auto">
            <div class="modal-header border-0">
                <h3 class="mg-section-title h5 mb-0">Editar meta mensal</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0">
                <label class="form-label small text-muted">Valor da meta</label>
                <input type="text" class="form-control" value="R$ {{ number_format($stats['monthly_goal'], 2, ',', '.') }}" readonly>
                <p class="mg-panel-hint mt-2 mb-0">Em breve: salvar meta personalizada.</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.mg-pay-toggle-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.mg-pay-toggle-btn').forEach(function (b) { b.classList.remove('is-active'); });
            btn.classList.add('is-active');
        });
    });
    var hideBtn = document.getElementById('mgHideValues');
    var hideBtnSales = document.getElementById('mgHideValuesSales');
    function syncHideValuesLabel(hidden) {
        var html = hidden
            ? '<i class="ri-eye-line"></i> Mostrar valores'
            : '<i class="ri-eye-off-line"></i> Ocultar valores';
        [hideBtn, hideBtnSales].forEach(function (btn) {
            if (btn) btn.innerHTML = html;
        });
    }
    function toggleHideValues() {
        document.body.classList.toggle('mg-hide-values');
        syncHideValuesLabel(document.body.classList.contains('mg-hide-values'));
    }
    if (hideBtn) hideBtn.addEventListener('click', toggleHideValues);
    if (hideBtnSales) hideBtnSales.addEventListener('click', toggleHideValues);

    var detailBtn = document.getElementById('mgHideDetail');
    var detailBtnSales = document.getElementById('mgHideDetailSales');
    var salesDetail = document.getElementById('mgSalesDetail');
    function syncDetailLabel(hidden) {
        var html = hidden
            ? '<i class="ri-layout-row-line"></i> Mostrar detalhamento'
            : '<i class="ri-layout-row-line"></i> Ocultar detalhamento';
        [detailBtn, detailBtnSales].forEach(function (btn) {
            if (btn) btn.innerHTML = html;
        });
    }
    function toggleDetail() {
        if (salesDetail) salesDetail.classList.toggle('d-none');
        var revenue = document.getElementById('mgRevenueSection');
        if (revenue) revenue.querySelector('.mg-revenue-compare-col')?.classList.toggle('d-none');
        var hidden = salesDetail ? salesDetail.classList.contains('d-none') : false;
        syncDetailLabel(hidden);
    }
    if (detailBtn) detailBtn.addEventListener('click', toggleDetail);
    if (detailBtnSales) detailBtnSales.addEventListener('click', toggleDetail);
});
</script>
@endpush
@endsection
