@extends('layouts.master')

@section('title', 'Prescrições')

@section('content')
<div class="mg-clients-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Prescrições</h1>
            <p class="mg-page-sub mb-0">Organize prescrições de treino e dieta por expiração, aluno e tipo.</p>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter">
                    <i class="ri-file-list-3-line"></i>
                    {{ $stats['total'] }} {{ $stats['total'] === 1 ? 'prescrição' : 'prescrições' }}
                </span>
                <span class="mg-clients-counter">
                    <i class="ri-restaurant-line"></i>
                    {{ $stats['diets'] }} dietas
                </span>
            </div>
        </div>
        <div class="mg-clients-toolbar__right">
            <a href="{{ route('workouts.create') }}" class="mg-btn-ghost">
                <i class="ri-add-line"></i> Novo treino
            </a>
            <a href="{{ route('members.index') }}" class="mg-btn-primary">
                <i class="ri-group-line"></i> Clientes
            </a>
        </div>
    </div>

    <div class="mg-clients-filters mg-client-directory-filters">
        <form method="GET" action="{{ route('prescriptions.index') }}" class="mg-clients-filters__form p-0">
            <div class="mg-client-directory-filters__grid">
                <div class="mg-client-directory-filters__search">
                    <label for="prescriptionSearch" class="mg-field-label">Buscar</label>
                    <div class="mg-field-with-icon">
                        <i class="ri-search-line"></i>
                        <input id="prescriptionSearch" type="search" name="q" value="{{ $filters['q'] }}" class="mg-field" placeholder="Nome, email ou prescrição">
                    </div>
                </div>

                <div>
                    <label for="prescriptionDays" class="mg-field-label">Próximos dias</label>
                    <select id="prescriptionDays" name="days" class="mg-field">
                        <option value="7" @selected($filters['days'] === '7')>7 dias</option>
                        <option value="15" @selected($filters['days'] === '15')>15 dias</option>
                        <option value="30" @selected($filters['days'] === '30')>30 dias</option>
                        <option value="60" @selected($filters['days'] === '60')>60 dias</option>
                        <option value="90" @selected($filters['days'] === '90')>90 dias</option>
                        <option value="all" @selected($filters['days'] === 'all')>Todas</option>
                    </select>
                </div>

                <div>
                    <label for="prescriptionType" class="mg-field-label">Tipo dieta/treino</label>
                    <select id="prescriptionType" name="type" class="mg-field">
                        <option value="" @selected($filters['type'] === '')>Todos</option>
                        <option value="workout" @selected($filters['type'] === 'workout')>Treino</option>
                        <option value="diet" @selected($filters['type'] === 'diet')>Dieta</option>
                    </select>
                </div>

                <div>
                    <label for="prescriptionDate" class="mg-field-label">Data</label>
                    <input id="prescriptionDate" type="date" name="date" value="{{ $filters['date'] }}" class="mg-field">
                </div>

                <div>
                    <label for="prescriptionSort" class="mg-field-label">Ordenar</label>
                    <select id="prescriptionSort" name="sort" class="mg-field">
                        <option value="expires_at" @selected($filters['sort'] === 'expires_at')>Data expiração</option>
                        <option value="name" @selected($filters['sort'] === 'name')>Nome</option>
                        <option value="type" @selected($filters['sort'] === 'type')>Tipo</option>
                    </select>
                </div>

                <div>
                    <label for="prescriptionDirection" class="mg-field-label">Direção</label>
                    <select id="prescriptionDirection" name="direction" class="mg-field">
                        <option value="asc" @selected($filters['direction'] === 'asc')>Crescente</option>
                        <option value="desc" @selected($filters['direction'] === 'desc')>Decrescente</option>
                    </select>
                </div>

                <div class="mg-clients-filters__actions">
                    <button type="submit" class="mg-btn-primary"><i class="ri-filter-3-line"></i> Filtrar</button>
                    <a href="{{ route('prescriptions.index') }}" class="mg-btn-ghost"><i class="ri-close-line"></i> Limpar</a>
                </div>
            </div>
        </form>
    </div>

    <div class="mg-stats-row">
        <div class="mg-stat-mini">
            <div class="mg-stat-label">Total</div>
            <div class="mg-stat-value">{{ $stats['total'] }}</div>
            <p class="mg-panel-hint mb-0">Base filtrada</p>
        </div>
        <div class="mg-stat-mini">
            <div class="mg-stat-label">Treinos</div>
            <div class="mg-stat-value">{{ $stats['workouts'] }}</div>
            <p class="mg-panel-hint mb-0">Workout local</p>
        </div>
        <div class="mg-stat-mini">
            <div class="mg-stat-label">Dietas</div>
            <div class="mg-stat-value">{{ $stats['diets'] }}</div>
            <p class="mg-panel-hint mb-0">DietPrescription local</p>
        </div>
        <div class="mg-stat-mini">
            <div class="mg-stat-label">Atrasadas</div>
            <div class="mg-stat-value text-danger">{{ $stats['late'] }}</div>
            <p class="mg-panel-hint mb-0">Status LATE</p>
        </div>
    </div>

    <div class="mg-panel mg-panel--compact">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="mg-section-title h6 mb-0">Prescrições agendadas</h2>
            <span class="mg-chip mg-chip--info">{{ $prescriptions->total() }} registros</span>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle mg-table-compact">
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
                                <div><span class="mg-chip {{ $prescription['chip'] }}">{{ $prescription['status'] }}</span></div>
                            </td>
                            <td>
                                <span class="mg-chip {{ $prescription['type'] === 'Treino' ? 'mg-chip--info' : 'mg-chip--warn' }}">
                                    <i class="{{ $prescription['type'] === 'Treino' ? 'ri-dumbbell-line' : 'ri-restaurant-line' }}"></i>
                                    {{ $prescription['type'] }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ $prescription['url'] }}" class="mg-btn-ghost mg-btn-ghost--sm">
                                    <i class="ri-arrow-right-s-line"></i> Abrir
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="mg-empty-state mg-empty-state--compact">
                                    <i class="ri-file-list-3-line"></i>
                                    <p>Nenhuma prescrição encontrada com os filtros atuais.</p>
                                    <a href="{{ route('prescriptions.index') }}" class="mg-btn-ghost">Limpar filtros</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($prescriptions->hasPages())
        <div class="mg-pagination">{{ $prescriptions->links() }}</div>
    @endif
</div>
@endsection
