@extends('layouts.master')

@section('title', 'Prescrições')

@section('content')
<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Prescrições</h1>
            <p class="prime-page-sub mb-0">Organize prescrições de treino e dieta por expiração, aluno e tipo.</p>
            <div class="prime-clients-counters">
                <span class="prime-clients-counter">
                    <i class="ri-file-list-3-line"></i>
                    {{ $stats['total'] }} {{ $stats['total'] === 1 ? 'prescrição' : 'prescrições' }}
                </span>
                <span class="prime-clients-counter">
                    <i class="ri-restaurant-line"></i>
                    {{ $stats['diets'] }} dietas
                </span>
            </div>
        </div>
        <div class="prime-clients-toolbar__right">
            <a href="{{ route('workouts.create') }}" class="prime-btn-ghost">
                <i class="ri-add-line"></i> Novo treino
            </a>
            <a href="{{ route('members.index') }}" class="prime-btn-primary">
                <i class="ri-group-line"></i> Clientes
            </a>
        </div>
    </div>

    <div class="prime-clients-filters prime-client-directory-filters">
        <form method="GET" action="{{ route('prescriptions.index') }}" class="prime-clients-filters__form p-0">
            <div class="prime-client-directory-filters__grid">
                <div class="prime-client-directory-filters__search">
                    <label for="prescriptionSearch" class="prime-field-label">Buscar</label>
                    <div class="prime-field-with-icon">
                        <i class="ri-search-line"></i>
                        <input id="prescriptionSearch" type="search" name="q" value="{{ $filters['q'] }}" class="prime-field" placeholder="Nome, email ou prescrição">
                    </div>
                </div>

                <div>
                    <label for="prescriptionDays" class="prime-field-label">Próximos dias</label>
                    <select id="prescriptionDays" name="days" class="prime-field">
                        <option value="7" @selected($filters['days'] === '7')>7 dias</option>
                        <option value="15" @selected($filters['days'] === '15')>15 dias</option>
                        <option value="30" @selected($filters['days'] === '30')>30 dias</option>
                        <option value="60" @selected($filters['days'] === '60')>60 dias</option>
                        <option value="90" @selected($filters['days'] === '90')>90 dias</option>
                        <option value="all" @selected($filters['days'] === 'all')>Todas</option>
                    </select>
                </div>

                <div>
                    <label for="prescriptionType" class="prime-field-label">Tipo dieta/treino</label>
                    <select id="prescriptionType" name="type" class="prime-field">
                        <option value="" @selected($filters['type'] === '')>Todos</option>
                        <option value="workout" @selected($filters['type'] === 'workout')>Treino</option>
                        <option value="diet" @selected($filters['type'] === 'diet')>Dieta</option>
                    </select>
                </div>

                <div>
                    <label for="prescriptionDate" class="prime-field-label">Data</label>
                    <input id="prescriptionDate" type="date" name="date" value="{{ $filters['date'] }}" class="prime-field">
                </div>

                <div>
                    <label for="prescriptionSort" class="prime-field-label">Ordenar</label>
                    <select id="prescriptionSort" name="sort" class="prime-field">
                        <option value="expires_at" @selected($filters['sort'] === 'expires_at')>Data expiração</option>
                        <option value="name" @selected($filters['sort'] === 'name')>Nome</option>
                        <option value="type" @selected($filters['sort'] === 'type')>Tipo</option>
                    </select>
                </div>

                <div>
                    <label for="prescriptionDirection" class="prime-field-label">Direção</label>
                    <select id="prescriptionDirection" name="direction" class="prime-field">
                        <option value="asc" @selected($filters['direction'] === 'asc')>Crescente</option>
                        <option value="desc" @selected($filters['direction'] === 'desc')>Decrescente</option>
                    </select>
                </div>

                <div class="prime-clients-filters__actions">
                    <button type="submit" class="prime-btn-primary"><i class="ri-filter-3-line"></i> Filtrar</button>
                    <a href="{{ route('prescriptions.index') }}" class="prime-btn-ghost"><i class="ri-close-line"></i> Limpar</a>
                </div>
            </div>
        </form>
    </div>

    <div class="prime-stats-row">
        <div class="prime-stat-mini">
            <div class="prime-stat-label">Total</div>
            <div class="prime-stat-value">{{ $stats['total'] }}</div>
            <p class="prime-panel-hint mb-0">Base filtrada</p>
        </div>
        <div class="prime-stat-mini">
            <div class="prime-stat-label">Treinos</div>
            <div class="prime-stat-value">{{ $stats['workouts'] }}</div>
            <p class="prime-panel-hint mb-0">Workout local</p>
        </div>
        <div class="prime-stat-mini">
            <div class="prime-stat-label">Dietas</div>
            <div class="prime-stat-value">{{ $stats['diets'] }}</div>
            <p class="prime-panel-hint mb-0">DietPrescription local</p>
        </div>
        <div class="prime-stat-mini">
            <div class="prime-stat-label">Atrasadas</div>
            <div class="prime-stat-value text-danger">{{ $stats['late'] }}</div>
            <p class="prime-panel-hint mb-0">Status LATE</p>
        </div>
    </div>

    <div class="prime-panel prime-panel--compact">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="prime-section-title h6 mb-0">Prescrições agendadas</h2>
            <span class="prime-chip prime-chip--info">{{ $prescriptions->total() }} registros</span>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle prime-table-compact">
                <thead>
                    <tr>
                        <th>Data expiração</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Prescrição</th>
                        <th>Tipo</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prescriptions as $prescription)
                        <tr>
                            <td>
                                @if($prescription['expires_at'])
                                    {{ $prescription['expires_at']->format('d/m/Y') }}
                                @else
                                    <span class="text-muted">Sem data</span>
                                @endif
                            </td>
                            <td>{{ $prescription['name'] }}</td>
                            <td>{{ $prescription['email'] }}</td>
                            <td>
                                <strong>{{ $prescription['title'] }}</strong>
                                <div><span class="prime-chip {{ $prescription['chip'] }}">{{ $prescription['status'] }}</span></div>
                            </td>
                            <td>
                                <span class="prime-chip {{ $prescription['type'] === 'Treino' ? 'prime-chip--info' : 'prime-chip--warn' }}">
                                    <i class="{{ $prescription['type'] === 'Treino' ? 'ri-dumbbell-line' : 'ri-restaurant-line' }}"></i>
                                    {{ $prescription['type'] }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ $prescription['url'] }}" class="prime-btn-ghost prime-btn-ghost--sm">
                                    <i class="ri-arrow-right-s-line"></i> Abrir
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="prime-empty-state prime-empty-state--compact">
                                    <i class="ri-file-list-3-line"></i>
                                    <p>Nenhuma prescrição encontrada com os filtros atuais.</p>
                                    <a href="{{ route('prescriptions.index') }}" class="prime-btn-ghost">Limpar filtros</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($prescriptions->hasPages())
        <div class="prime-pagination">{{ $prescriptions->links() }}</div>
    @endif
</div>
@endsection
