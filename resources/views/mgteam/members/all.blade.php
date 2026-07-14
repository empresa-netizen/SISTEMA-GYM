@extends('layouts.master')

@section('title', 'Clientes')

@section('content')
<div class="mg-clients-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Clientes</h1>
            <p class="mg-page-sub mb-0">Gerencie seus clientes</p>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter mg-clients-counter--delivered">
                    <i class="ri-group-fill"></i>
                    {{ $members->total() }} {{ $members->total() === 1 ? 'cliente encontrado' : 'clientes encontrados' }}
                </span>
            </div>
        </div>
        <div class="mg-clients-toolbar__right">
            <a href="{{ route('members.all', ['birthday' => 'month']) }}" class="mg-btn-ghost"><i class="ri-cake-2-line"></i> Aniversariantes</a>
            <a href="{{ route('members.dropouts') }}" class="mg-btn-ghost"><i class="ri-delete-bin-6-line"></i> Excluídos</a>
            <button type="button" class="mg-btn-ghost" disabled title="Exportação ainda não conectada"><i class="ri-download-2-line"></i> Exportar lista</button>
            @can('create members')
                <a href="{{ route('members.create') }}" class="mg-btn-primary"><i class="ri-user-add-line"></i> Adicionar manualmente</a>
            @endcan
        </div>
    </div>

    <div class="mg-clients-filters mg-client-directory-filters">
        <form method="GET" action="{{ route('members.all') }}" class="mg-clients-filters__form p-0">
            <div class="mg-client-directory-filters__grid">
                <div class="mg-client-directory-filters__search">
                    <label for="memberSearch" class="mg-field-label">Buscar</label>
                    <div class="mg-field-with-icon">
                        <i class="ri-search-line"></i>
                        <input
                            id="memberSearch"
                            type="search"
                            name="q"
                            value="{{ $filters['q'] }}"
                            class="mg-field"
                            placeholder="Nome, email ou WhatsApp"
                        >
                    </div>
                </div>

                <div>
                    <label for="memberStatus" class="mg-field-label">Status do plano</label>
                    <select id="memberStatus" name="status" class="mg-field">
                        <option value="">Todos</option>
                        <option value="active" @selected($filters['status'] === 'active')>Ativo</option>
                        <option value="inactive" @selected($filters['status'] === 'inactive')>Inativo</option>
                        <option value="expired" @selected($filters['status'] === 'expired')>Expirado</option>
                        <option value="suspended" @selected($filters['status'] === 'suspended')>Suspenso</option>
                    </select>
                </div>

                <div>
                    <label for="memberPlan" class="mg-field-label">Plano</label>
                    <select id="memberPlan" name="plan" class="mg-field">
                        <option value="">Todos os planos</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" @selected((string) $filters['plan'] === (string) $plan->id)>{{ $plan->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="memberRenewal" class="mg-field-label">Vencimento próximo</label>
                    <select id="memberRenewal" name="renewal" class="mg-field">
                        <option value="">Qualquer vencimento</option>
                        <option value="7" @selected($filters['renewal'] === '7')>Próximos 7 dias</option>
                        <option value="15" @selected($filters['renewal'] === '15')>Próximos 15 dias</option>
                        <option value="30" @selected($filters['renewal'] === '30')>Próximos 30 dias</option>
                        <option value="expired" @selected($filters['renewal'] === 'expired')>Vencidos</option>
                        <option value="no_date" @selected($filters['renewal'] === 'no_date')>Sem vencimento</option>
                    </select>
                </div>

                <div>
                    <label for="memberAppInstalled" class="mg-field-label">App instalado</label>
                    <select id="memberAppInstalled" name="app_installed" class="mg-field" title="Filtro visual: sem coluna local equivalente">
                        <option value="">Todos</option>
                        <option value="yes" @selected($filters['app_installed'] === 'yes')>Sim</option>
                        <option value="no" @selected($filters['app_installed'] === 'no')>Não</option>
                    </select>
                </div>

                <div>
                    <label for="memberAutomaticBilling" class="mg-field-label">Cobranças automáticas</label>
                    <select id="memberAutomaticBilling" name="automatic_billing" class="mg-field" title="Filtro visual: sem coluna local equivalente">
                        <option value="">Todas</option>
                        <option value="enabled" @selected($filters['automatic_billing'] === 'enabled')>Ativas</option>
                        <option value="disabled" @selected($filters['automatic_billing'] === 'disabled')>Inativas</option>
                    </select>
                </div>

                <div>
                    <label for="memberSort" class="mg-field-label">Ordenar por</label>
                    <select id="memberSort" name="sort" class="mg-field">
                        <option value="name_asc" @selected($filters['sort'] === 'name_asc')>Nome A-Z</option>
                        <option value="created_desc" @selected($filters['sort'] === 'created_desc')>Mais recentes</option>
                        <option value="renewal_asc" @selected($filters['sort'] === 'renewal_asc')>Vencimento mais próximo</option>
                        <option value="renewal_desc" @selected($filters['sort'] === 'renewal_desc')>Vencimento mais distante</option>
                    </select>
                </div>

                <div class="mg-clients-filters__actions">
                    <button type="submit" class="mg-btn-primary"><i class="ri-filter-3-line"></i> Filtrar</button>
                    <a href="{{ route('members.all') }}" class="mg-btn-ghost"><i class="ri-close-line"></i> Limpar</a>
                </div>
            </div>
        </form>
    </div>

    <div class="mg-client-list mg-client-directory-list">
        @forelse($members as $member)
            @php
                $initials = collect(explode(' ', $member->name))->filter()->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
                $statusLabels = ['active' => 'Ativo', 'inactive' => 'Inativo', 'expired' => 'Expirado', 'suspended' => 'Suspenso'];
                $statusClasses = ['active' => 'is-ok', 'inactive' => 'is-missing', 'expired' => 'is-warn', 'suspended' => 'is-missing'];
                $profileUrl = route('members.show', [$member, 'tab' => 'progress']);
                $renewalUrl = route('members.show', [$member, 'tab' => 'progress', 'renewal' => 1]);
                $waPhone = $member->phone ? preg_replace('/\D+/', '', $member->phone) : null;
                $expiresAt = $member->membership_end_date;
            @endphp
            <div class="mg-client-card mg-client-directory-row">
                <div class="mg-client-card__main">
                    <a href="{{ $profileUrl }}" class="mg-client-card__avatar-link">
                        @if($member->photo)
                            <img src="{{ asset('storage/'.$member->photo) }}" alt="" class="mg-client-card__avatar-img">
                        @else
                            <div class="mg-client-card__avatar">{{ strtoupper($initials) }}</div>
                        @endif
                    </a>
                    <div class="mg-client-card__identity">
                        <a href="{{ $profileUrl }}" class="mg-client-card__name">{{ $member->name }}</a>
                        <div class="mg-client-card__meta">
                            <span>{{ $member->email }}</span>
                            @if($member->phone)<span class="mg-client-card__sep">|</span><span>{{ $member->phone }}</span>@endif
                        </div>
                        <div class="mg-client-chips">
                            <span class="mg-status-badge {{ $statusClasses[$member->status] ?? 'is-missing' }}">
                                <i class="ri-checkbox-circle-line"></i> {{ $statusLabels[$member->status] ?? $member->status }}
                            </span>
                            @if($member->membershipPlan)
                                <span class="mg-chip mg-chip--info"><i class="ri-vip-crown-line"></i> {{ $member->membershipPlan->name }}</span>
                            @else
                                <span class="mg-chip"><i class="ri-price-tag-3-line"></i> Sem plano</span>
                            @endif
                            @if($expiresAt)
                                <span class="mg-chip {{ $expiresAt->isPast() ? 'mg-chip--danger' : ($expiresAt->lte(now()->addDays(30)) ? 'mg-chip--warn' : '') }}">
                                    <i class="ri-calendar-event-line"></i> Vence {{ $expiresAt->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="mg-chip"><i class="ri-calendar-close-line"></i> Sem vencimento</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="mg-client-directory-row__plan">
                    <span>Plano</span>
                    <strong>{{ $member->membershipPlan?->name ?? 'Não definido' }}</strong>
                </div>
                <div class="mg-client-card__actions">
                    <form method="POST" action="{{ route('messages.start', $member) }}">
                        @csrf
                        <button type="submit" class="mg-btn-ghost mg-btn-ghost--sm"><i class="ri-message-3-line"></i> Enviar mensagem</button>
                    </form>
                    <button type="button" class="mg-icon-btn mg-copy-btn" data-copy-value="{{ $profileUrl }}" title="Copiar link do cliente">
                        <i class="ri-file-copy-line"></i>
                    </button>
                    <div class="dropdown">
                        <button type="button" class="mg-icon-btn" data-bs-toggle="dropdown" aria-expanded="false" title="Ações">
                            <i class="ri-more-2-fill"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end mg-dropdown">
                            <li>
                                <button type="button" class="dropdown-item mg-copy-btn" data-copy-value="{{ $renewalUrl }}">
                                    <i class="ri-link-m me-2"></i> Gerar link renovação
                                </button>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('members.edit', $member) }}">
                                    <i class="ri-pencil-line me-2"></i> Editar
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('members.destroy', $member) }}" data-mg-member-delete>
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="ri-delete-bin-6-line me-2"></i> Excluir
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                    @if($waPhone)
                        <a href="https://wa.me/{{ $waPhone }}" target="_blank" rel="noopener" class="mg-icon-btn mg-icon-btn--whatsapp" title="WhatsApp">
                            <i class="ri-whatsapp-line"></i>
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="mg-empty-state">
                <i class="ri-group-line"></i>
                <p>Nenhum cliente encontrado com os filtros atuais.</p>
                <a href="{{ route('members.all') }}" class="mg-btn-ghost">Limpar filtros</a>
            </div>
        @endforelse
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

    document.querySelectorAll('[data-mg-member-delete]').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (!confirm('Excluir este cliente?')) {
                return;
            }

            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: new FormData(form),
            });

            if (response.ok) {
                window.location.reload();
            }
        });
    });
</script>
@endpush
