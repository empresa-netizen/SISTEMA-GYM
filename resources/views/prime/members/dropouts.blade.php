@extends('layouts.master')

@section('title', 'Desistências')

@section('content')
@php
    $money = fn ($value) => 'R$ ' . number_format((float) $value, 2, ',', '.');
    $periodLabels = [
        '7' => 'últimos 7 dias',
        '30' => 'últimos 30 dias',
        '60' => 'últimos 60 dias',
        '90' => 'últimos 90 dias',
        'all' => 'todo o histórico',
    ];
@endphp

<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Desistências</h1>
            <p class="prime-page-sub mb-0">Seus alunos que não renovaram</p>
            <div class="prime-clients-counters">
                <span class="prime-clients-counter prime-clients-counter--pending">
                    <i class="ri-user-unfollow-line"></i>
                    {{ $stats['total_clients'] }} {{ $stats['total_clients'] === 1 ? 'aluno' : 'alunos' }}
                </span>
                <span class="prime-clients-counter">
                    <i class="ri-calendar-close-line"></i>
                    {{ $stats['expired_count'] }} vencidos
                </span>
            </div>
        </div>
        <div class="prime-clients-toolbar__right">
            <a href="{{ route('members.all') }}" class="prime-btn-ghost">
                <i class="ri-group-line"></i> Todos
            </a>
            <a href="{{ route('members.dropouts', array_merge(request()->query(), ['export' => 1])) }}" class="prime-btn-primary">
                <i class="ri-download-2-line"></i> Exportar lista
            </a>
        </div>
    </div>

    <div class="prime-clients-filters prime-client-directory-filters">
        <form method="GET" action="{{ route('members.dropouts') }}" class="prime-clients-filters__form p-0">
            <div class="prime-client-directory-filters__grid">
                <div class="prime-client-directory-filters__search">
                    <label for="dropoutSearch" class="prime-field-label">Buscar</label>
                    <div class="prime-field-with-icon">
                        <i class="ri-search-line"></i>
                        <input id="dropoutSearch" type="search" name="q" value="{{ $filters['q'] }}" class="prime-field" placeholder="Nome, email ou WhatsApp">
                    </div>
                </div>

                <div>
                    <label for="dropoutPlan" class="prime-field-label">Plano</label>
                    <select id="dropoutPlan" name="plan" class="prime-field">
                        <option value="">Todos os planos</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" @selected((string) $filters['plan'] === (string) $plan->id)>{{ $plan->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="dropoutPeriod" class="prime-field-label">Período</label>
                    <select id="dropoutPeriod" name="period" class="prime-field">
                        <option value="7" @selected($filters['period'] === '7')>Últimos 7 dias</option>
                        <option value="30" @selected($filters['period'] === '30')>Últimos 30 dias</option>
                        <option value="60" @selected($filters['period'] === '60')>Últimos 60 dias</option>
                        <option value="90" @selected($filters['period'] === '90')>Últimos 90 dias</option>
                        <option value="all" @selected($filters['period'] === 'all')>Todo o histórico</option>
                    </select>
                </div>

                <div>
                    <label class="prime-field-label">Reembolsos</label>
                    <label class="prime-check-row">
                        <input type="checkbox" name="show_refunded" value="1" @checked($filters['show_refunded'])>
                        <span>Mostrar reembolsados</span>
                    </label>
                </div>

                <div class="prime-clients-filters__actions">
                    <button type="submit" class="prime-btn-primary"><i class="ri-filter-3-line"></i> Filtrar</button>
                    <a href="{{ route('members.dropouts') }}" class="prime-btn-ghost"><i class="ri-close-line"></i> Limpar</a>
                </div>
            </div>
        </form>
    </div>

    <div class="prime-stats-row">
        <div class="prime-stat-mini">
            <div class="prime-stat-label">Base filtrada</div>
            <div class="prime-stat-value">{{ $stats['total_clients'] }}</div>
            <p class="prime-panel-hint mb-0">{{ $periodLabels[$filters['period']] ?? 'período selecionado' }}</p>
        </div>
        <div class="prime-stat-mini">
            <div class="prime-stat-label">Receita em risco</div>
            <div class="prime-stat-value prime-money-value">{{ $money($stats['potential_revenue']) }}</div>
            <p class="prime-panel-hint mb-0">Planos locais vinculados</p>
        </div>
        <div class="prime-stat-mini">
            <div class="prime-stat-label">Suspensos</div>
            <div class="prime-stat-value">{{ $stats['suspended_count'] }}</div>
            <p class="prime-panel-hint mb-0">Status local suspenso</p>
        </div>
        <div class="prime-stat-mini">
            <div class="prime-stat-label">Reembolsados</div>
            <div class="prime-stat-value">{{ $stats['refunded_count'] }}</div>
            <p class="prime-panel-hint mb-0">{{ $filters['show_refunded'] ? 'Incluídos' : 'Ocultos por padrão' }}</p>
        </div>
    </div>

    <div class="prime-panel prime-panel--compact">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="prime-section-title h6 mb-0">Alunos sem renovação</h2>
            <span class="prime-chip prime-chip--danger">{{ $members->total() }} registros</span>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle prime-table-compact">
                <thead>
                    <tr>
                        <th>Aluno</th>
                        <th>Plano</th>
                        <th>Vencimento</th>
                        <th>Status</th>
                        <th>WhatsApp</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
        @forelse($members as $member)
            @php
                $initials = collect(explode(' ', $member->name))->filter()->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
                $profileUrl = route('members.show', [$member, 'tab' => 'progress']);
                $renewalUrl = route('members.show', [$member, 'tab' => 'progress', 'renewal' => 1]);
                $waPhone = $member->phone ? preg_replace('/\D+/', '', $member->phone) : null;
                $statusLabels = ['inactive' => 'Inativo', 'expired' => 'Expirado', 'suspended' => 'Suspenso', 'active' => 'Vencido'];
                $isRefunded = ($member->refunded_payment_transactions_count ?? 0) > 0;
                $days = $member->membership_end_date ? (int) now()->diffInDays($member->membership_end_date, false) : null;
            @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <a href="{{ $profileUrl }}" class="prime-client-card__avatar-link">
                                    @if($member->photo)
                                        <img src="{{ asset('storage/'.$member->photo) }}" alt="" class="prime-client-card__avatar-img">
                                    @else
                                        <div class="prime-client-card__avatar">{{ strtoupper($initials) }}</div>
                                    @endif
                                </a>
                                <div>
                                    <a href="{{ $profileUrl }}" class="prime-client-card__name">{{ $member->name }}</a>
                                    <div class="prime-client-card__meta">{{ $member->email ?: 'Sem email' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <strong>{{ $member->membershipPlan?->name ?? 'Sem plano' }}</strong>
                            <div class="prime-client-card__meta prime-money-value">{{ $money($member->membershipPlan?->price ?? 0) }}</div>
                        </td>
                        <td>
                            @if($member->membership_end_date)
                                <div>{{ $member->membership_end_date->format('d/m/Y') }}</div>
                                @if($days < 0)
                                    <span class="prime-chip prime-chip--danger"><i class="ri-time-line"></i> Vencido há {{ abs($days) }}d</span>
                                @else
                                    <span class="prime-chip prime-chip--warn"><i class="ri-time-line"></i> Vence em {{ $days }}d</span>
                                @endif
                            @else
                                <span class="prime-chip"><i class="ri-calendar-close-line"></i> Sem vencimento</span>
                            @endif
                        </td>
                        <td>
                            <div class="prime-client-chips">
                                <span class="prime-status-badge is-missing">
                                    <i class="ri-user-unfollow-line"></i> {{ $statusLabels[$member->status] ?? ucfirst($member->status) }}
                                </span>
                                @if($isRefunded)
                                    <span class="prime-chip prime-chip--warn"><i class="ri-refund-2-line"></i> Reembolsado</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($waPhone)
                                <a href="https://wa.me/{{ $waPhone }}" target="_blank" rel="noopener" class="prime-icon-btn prime-icon-btn--whatsapp" title="Abrir WhatsApp">
                                    <i class="ri-whatsapp-line"></i>
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="prime-btn-ghost prime-btn-ghost--sm prime-copy-btn" data-copy-value="{{ $member->email }}" @disabled(! $member->email)>
                                    <i class="ri-file-copy-line"></i> Copiar email
                                </button>
                                <button type="button" class="prime-btn-ghost prime-btn-ghost--sm prime-copy-btn" data-copy-value="{{ $renewalUrl }}">
                                    <i class="ri-link-m"></i> Copiar link renovação
                                </button>
                                <a href="{{ $profileUrl }}" class="prime-icon-btn" title="Abrir aluno">
                                    <i class="ri-arrow-right-s-line"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
        @empty
                    <tr>
                        <td colspan="6">
                            <div class="prime-empty-state prime-empty-state--compact">
                                <i class="ri-emotion-happy-line"></i>
                                <p>Nenhuma desistência encontrada com os filtros atuais.</p>
                                <a href="{{ route('members.dropouts') }}" class="prime-btn-ghost">Limpar filtros</a>
                            </div>
                        </td>
                    </tr>
        @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="prime-client-list d-lg-none">
        @foreach($members as $member)
            @php
                $initials = collect(explode(' ', $member->name))->filter()->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
                $profileUrl = route('members.show', [$member, 'tab' => 'progress']);
                $renewalUrl = route('members.show', [$member, 'tab' => 'progress', 'renewal' => 1]);
                $waPhone = $member->phone ? preg_replace('/\D+/', '', $member->phone) : null;
            @endphp
            <div class="prime-client-card">
                <div class="prime-client-card__main">
                    <a href="{{ $profileUrl }}" class="prime-client-card__avatar-link">
                        @if($member->photo)
                            <img src="{{ asset('storage/'.$member->photo) }}" alt="" class="prime-client-card__avatar-img">
                        @else
                            <div class="prime-client-card__avatar">{{ strtoupper($initials) }}</div>
                        @endif
                    </a>
                    <div class="prime-client-card__identity">
                        <a href="{{ $profileUrl }}" class="prime-client-card__name">{{ $member->name }}</a>
                        <div class="prime-client-card__meta">
                            <span>{{ $member->membershipPlan?->name ?? 'Sem plano' }}</span>
                            @if($member->phone)
                                <span class="prime-client-card__sep">|</span>
                                <span>{{ $member->phone }}</span>
                            @endif
                        </div>
                        <div class="prime-client-chips">
                            <span class="prime-chip prime-chip--danger"><i class="ri-user-unfollow-line"></i> Sem renovação</span>
                            @if($member->membership_end_date)
                                <span class="prime-chip"><i class="ri-calendar-event-line"></i> {{ $member->membership_end_date->format('d/m/Y') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="prime-client-card__actions">
                    @if($waPhone)
                        <a href="https://wa.me/{{ $waPhone }}" target="_blank" rel="noopener" class="prime-icon-btn prime-icon-btn--whatsapp" title="WhatsApp">
                            <i class="ri-whatsapp-line"></i>
                        </a>
                    @endif
                    <button type="button" class="prime-icon-btn prime-copy-btn" data-copy-value="{{ $member->email }}" @disabled(! $member->email) title="Copiar email">
                        <i class="ri-file-copy-line"></i>
                    </button>
                    <button type="button" class="prime-icon-btn prime-copy-btn" data-copy-value="{{ $renewalUrl }}" title="Copiar link renovação">
                        <i class="ri-link-m"></i>
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    @if($members->hasPages())
        <div class="prime-pagination">{{ $members->links() }}</div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.prime-copy-btn').forEach((button) => {
        button.addEventListener('click', async () => {
            const value = button.dataset.copyValue;

            if (!value || !navigator.clipboard) {
                return;
            }

            await navigator.clipboard.writeText(value);
            button.classList.add('is-copied');
            setTimeout(() => button.classList.remove('is-copied'), 1200);
        });
    });
</script>
@endpush
