@extends('layouts.master')

@section('title', 'Estimativa de Renovações')

@section('content')
@php
    $money = fn ($value) => 'R$ ' . number_format((float) $value, 2, ',', '.');
@endphp

<div class="mg-clients-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Estimativa de Renovações</h1>
            <p class="mg-page-sub mb-0">Acompanhe vencimentos próximos e a receita potencial de renovações.</p>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter">
                    <i class="ri-calendar-check-line"></i>
                    {{ $stats['total_clients'] }} {{ $stats['total_clients'] === 1 ? 'cliente' : 'clientes' }}
                </span>
                <span class="mg-clients-counter mg-clients-counter--pending">
                    <i class="ri-error-warning-fill"></i>
                    {{ $stats['urgent_count'] }} em até 7 dias
                </span>
            </div>
        </div>
        <div class="mg-clients-toolbar__right">
            <a href="{{ route('members.index') }}" class="mg-btn-ghost">
                <i class="ri-group-line"></i> Clientes ativos
            </a>
            <button type="button" class="mg-btn-primary" disabled title="Exportação local ainda não conectada">
                <i class="ri-download-2-line"></i> Exportar lista
            </button>
        </div>
    </div>

    <div class="mg-clients-filters mg-client-directory-filters">
        <form method="GET" action="{{ route('members.renewals') }}" class="mg-clients-filters__form p-0">
            <div class="mg-client-directory-filters__grid">
                <div class="mg-client-directory-filters__search">
                    <label for="renewalSearch" class="mg-field-label">Buscar</label>
                    <div class="mg-field-with-icon">
                        <i class="ri-search-line"></i>
                        <input id="renewalSearch" type="search" name="q" value="{{ $filters['q'] }}" class="mg-field" placeholder="Nome, email ou WhatsApp">
                    </div>
                </div>

                <div>
                    <label for="renewalPeriod" class="mg-field-label">Período</label>
                    <select id="renewalPeriod" name="period" class="mg-field">
                        <option value="7" @selected($filters['period'] === '7')>Próximos 7 dias</option>
                        <option value="15" @selected($filters['period'] === '15')>Próximos 15 dias</option>
                        <option value="30" @selected($filters['period'] === '30')>Próximos 30 dias</option>
                        <option value="60" @selected($filters['period'] === '60')>Próximos 60 dias</option>
                        <option value="90" @selected($filters['period'] === '90')>Próximos 90 dias</option>
                        <option value="expired" @selected($filters['period'] === 'expired')>Vencidos</option>
                        <option value="all" @selected($filters['period'] === 'all')>Todos com vencimento</option>
                    </select>
                </div>

                <div>
                    <label for="renewalPlan" class="mg-field-label">Plano</label>
                    <select id="renewalPlan" name="plan" class="mg-field">
                        <option value="">Todos os planos</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" @selected((string) $filters['plan'] === (string) $plan->id)>{{ $plan->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="renewalAmount" class="mg-field-label">Filtro valor</label>
                    <select id="renewalAmount" name="amount" class="mg-field">
                        <option value="">Qualquer valor</option>
                        <option value="with_value" @selected($filters['amount'] === 'with_value')>Com valor</option>
                        <option value="no_value" @selected($filters['amount'] === 'no_value')>Sem valor</option>
                        <option value="under_100" @selected($filters['amount'] === 'under_100')>Até R$ 99,99</option>
                        <option value="100_300" @selected($filters['amount'] === '100_300')>R$ 100 a R$ 300</option>
                        <option value="over_300" @selected($filters['amount'] === 'over_300')>Acima de R$ 300</option>
                    </select>
                </div>

                <div>
                    <label for="renewalStatus" class="mg-field-label">Status renovação</label>
                    <select id="renewalStatus" name="renewal_status" class="mg-field">
                        <option value="upcoming" @selected($filters['renewal_status'] === 'upcoming')>A vencer</option>
                        <option value="urgent" @selected($filters['renewal_status'] === 'urgent')>Urgente</option>
                        <option value="expired" @selected($filters['renewal_status'] === 'expired')>Vencido</option>
                        <option value="renewed" @selected($filters['renewal_status'] === 'renewed')>Efetivado</option>
                        <option value="no_date" @selected($filters['renewal_status'] === 'no_date')>Sem vencimento</option>
                    </select>
                </div>

                <div>
                    <label for="renewalSort" class="mg-field-label">Ordenar</label>
                    <select id="renewalSort" name="sort" class="mg-field">
                        <option value="membership_end_date" @selected($filters['sort'] === 'membership_end_date')>Vencimento</option>
                        <option value="name" @selected($filters['sort'] === 'name')>Nome</option>
                        <option value="plan" @selected($filters['sort'] === 'plan')>Plano</option>
                        <option value="value" @selected($filters['sort'] === 'value')>Valor</option>
                    </select>
                </div>

                <div>
                    <label for="renewalDirection" class="mg-field-label">Direção</label>
                    <select id="renewalDirection" name="direction" class="mg-field">
                        <option value="asc" @selected($filters['direction'] === 'asc')>Crescente</option>
                        <option value="desc" @selected($filters['direction'] === 'desc')>Decrescente</option>
                    </select>
                </div>

                <div>
                    <label class="mg-field-label">Recorrência</label>
                    <label class="mg-check-row">
                        <input type="checkbox" name="recurring" value="1" @checked($filters['recurring'])>
                        <span>Somente recorrentes</span>
                    </label>
                    <label class="mg-check-row">
                        <input type="checkbox" name="history" value="1" @checked($filters['history'])>
                        <span>Com histórico</span>
                    </label>
                </div>

                <div class="mg-clients-filters__actions">
                    <button type="submit" class="mg-btn-primary"><i class="ri-filter-3-line"></i> Filtrar</button>
                    <a href="{{ route('members.renewals') }}" class="mg-btn-ghost"><i class="ri-close-line"></i> Limpar</a>
                </div>
            </div>
        </form>
    </div>

    <div class="mg-stats-row">
        <div class="mg-stat-mini">
            <div class="mg-stat-label">Total clientes</div>
            <div class="mg-stat-value">{{ $stats['total_clients'] }}</div>
            <p class="mg-panel-hint mb-0">Base filtrada</p>
        </div>
        <div class="mg-stat-mini">
            <div class="mg-stat-label">Receita potencial</div>
            <div class="mg-stat-value mg-money-value">{{ $money($stats['potential_revenue']) }}</div>
            <p class="mg-panel-hint mb-0">Planos vinculados</p>
        </div>
        <div class="mg-stat-mini">
            <div class="mg-stat-label">Renovações efetivadas</div>
            <div class="mg-stat-value text-success">{{ $stats['renewed_count'] }}</div>
            <p class="mg-panel-hint mb-0">Pagamentos locais recentes</p>
        </div>
        <div class="mg-stat-mini">
            <div class="mg-stat-label">Taxa</div>
            <div class="mg-stat-value">{{ number_format($stats['renewal_rate'], 1, ',', '.') }}%</div>
            <p class="mg-panel-hint mb-0">{{ $stats['expired_count'] }} vencidos</p>
        </div>
    </div>

    <div class="mg-panel mg-panel--compact">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="mg-section-title h6 mb-0">Clientes em renovação</h2>
            <span class="mg-chip mg-chip--info">{{ $members->total() }} registros</span>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle mg-table-compact">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Vencimento</th>
                        <th>Plano</th>
                        <th class="text-end">Valor</th>
                        <th>WhatsApp</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
        @forelse($members as $member)
            @php
                $days = $member->membership_end_date ? (int) now()->diffInDays($member->membership_end_date, false) : null;
                $initials = collect(explode(' ', $member->name))->filter()->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
                $waPhone = $member->phone ? preg_replace('/\D+/', '', $member->phone) : null;
                $profileUrl = route('members.show', [$member, 'tab' => 'progress']);
                $renewalUrl = route('members.show', [$member, 'tab' => 'progress', 'renewal' => 1]);
                $isRenewed = $renewedMemberIds->contains($member->id);
            @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <a href="{{ $profileUrl }}" class="mg-client-card__avatar-link">
                                    @if($member->photo)
                                        <img src="{{ asset('storage/'.$member->photo) }}" alt="" class="mg-client-card__avatar-img">
                                    @else
                                        <div class="mg-client-card__avatar">{{ strtoupper($initials) }}</div>
                                    @endif
                                </a>
                                <div>
                                    <a href="{{ $profileUrl }}" class="mg-client-card__name">{{ $member->name }}</a>
                                    <div class="mg-client-card__meta">{{ $member->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($member->membership_end_date)
                                <div>{{ $member->membership_end_date->format('d/m/Y') }}</div>
                                @if($isRenewed)
                                    <span class="mg-chip mg-chip--success"><i class="ri-checkbox-circle-line"></i> Efetivado</span>
                                @elseif($days < 0)
                                    <span class="mg-chip mg-chip--danger"><i class="ri-time-line"></i> Vencido há {{ abs($days) }}d</span>
                                @elseif($days <= 7)
                                    <span class="mg-chip mg-chip--danger"><i class="ri-alarm-warning-line"></i> {{ $days }}d restantes</span>
                                @else
                                    <span class="mg-chip mg-chip--warn"><i class="ri-time-line"></i> {{ $days }}d restantes</span>
                                @endif
                            @else
                                <span class="mg-chip"><i class="ri-calendar-close-line"></i> Sem vencimento</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $member->membershipPlan?->name ?? 'Sem plano' }}</strong>
                            @if(($member->active_subscriptions_count ?? 0) > 0)
                                <div><span class="mg-chip mg-chip--info">Recorrente</span></div>
                            @endif
                        </td>
                        <td class="text-end mg-money-value">{{ $money($member->membershipPlan?->price ?? 0) }}</td>
                        <td>
                            @if($waPhone)
                                <a href="https://wa.me/{{ $waPhone }}" target="_blank" rel="noopener" class="mg-icon-btn mg-icon-btn--whatsapp" title="Abrir WhatsApp">
                                    <i class="ri-whatsapp-line"></i>
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <form method="POST" action="{{ route('messages.start', $member) }}">
                                    @csrf
                                    <button type="submit" class="mg-btn-ghost mg-btn-ghost--sm"><i class="ri-message-3-line"></i> Mensagem</button>
                                </form>
                                <button type="button" class="mg-icon-btn mg-copy-btn" data-copy-value="{{ $renewalUrl }}" title="Copiar link de renovação">
                                    <i class="ri-link-m"></i>
                                </button>
                                <a href="{{ $profileUrl }}" class="mg-icon-btn" title="Abrir cliente">
                                    <i class="ri-arrow-right-s-line"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
        @empty
                    <tr>
                        <td colspan="6">
                            <div class="mg-empty-state mg-empty-state--compact">
                                <i class="ri-calendar-check-line"></i>
                                <p>Nenhuma renovação encontrada com os filtros atuais.</p>
                                <a href="{{ route('members.renewals') }}" class="mg-btn-ghost">Limpar filtros</a>
                            </div>
                        </td>
                    </tr>
        @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($members->hasPages())
        <div class="mg-pagination">{{ $members->links() }}</div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.mg-copy-btn').forEach((button) => {
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
