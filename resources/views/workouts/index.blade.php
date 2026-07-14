@extends('layouts.master')

@section('title', 'Treinos')

@section('content')
@php
    $filtersOpen = request()->hasAny(['search_value', 'member', 'status']);
    $statusChips = [
        'active' => ['Ativo', 'mg-chip--success'],
        'completed' => ['Concluído', 'mg-chip--info'],
        'cancelled' => ['Cancelado', 'mg-chip--danger'],
    ];
@endphp

<div class="mg-clients-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Prescrições de treino</h1>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter">
                    <i class="ri-dumbbell-line"></i>
                    {{ $workouts->total() }} treinos
                </span>
            </div>
        </div>
        <div class="mg-clients-toolbar__right">
            <a href="{{ route('workouts.today') }}" class="mg-btn-ghost">
                <i class="ri-calendar-check-line"></i> Treinos de hoje
            </a>
            <a href="{{ route('workouts.create') }}" class="mg-btn-primary">
                <i class="ri-add-line"></i> Nova prescrição
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="mg-clients-filters">
        <button type="button" class="mg-btn-ghost mg-filters-toggle {{ $filtersOpen ? 'is-open' : '' }}" data-bs-toggle="collapse" data-bs-target="#mgWorkoutsFilters" aria-expanded="{{ $filtersOpen ? 'true' : 'false' }}">
            <i class="ri-filter-3-line"></i> Filtros
            <i class="ri-arrow-down-s-line mg-filters-chevron"></i>
        </button>
        <div class="collapse {{ $filtersOpen ? 'show' : '' }}" id="mgWorkoutsFilters">
            <form action="{{ route('workouts.index') }}" method="get" class="mg-clients-filters__form">
                <div class="mg-clients-filters__grid">
                    <div>
                        <label class="mg-field-label">Buscar</label>
                        <input type="text" name="search_value" value="{{ request('search_value') }}" class="mg-field" placeholder="Nome ou ID do treino...">
                    </div>
                    <div>
                        <label class="mg-field-label">Cliente</label>
                        <select name="member" class="mg-field">
                            <option value="">Todos os clientes</option>
                            @foreach($members as $member)
                                <option value="{{ $member->id }}" @selected(request('member') == $member->id)>{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mg-field-label">Status</label>
                        <select name="status" class="mg-field">
                            <option value="">Todos</option>
                            <option value="active" @selected(request('status') === 'active')>Ativo</option>
                            <option value="completed" @selected(request('status') === 'completed')>Concluído</option>
                            <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelado</option>
                        </select>
                    </div>
                    <div class="mg-clients-filters__actions">
                        <button type="submit" class="mg-btn-primary">Aplicar</button>
                        <a href="{{ route('workouts.index') }}" class="mg-btn-ghost">Limpar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="mg-client-list">
        @forelse($workouts as $workout)
            @php
                [$statusLabel, $statusChip] = $statusChips[$workout->status] ?? [ucfirst($workout->status), ''];
            @endphp
            <a href="{{ route('workouts.show', $workout) }}" class="mg-client-card">
                <div class="mg-client-card__main">
                    <div class="mg-client-card__avatar" style="background:linear-gradient(135deg,#1d4ed8,#3b82f6)">
                        <i class="ri-dumbbell-line"></i>
                    </div>
                    <div class="mg-client-card__identity">
                        <div class="mg-client-card__name">{{ $workout->name }}</div>
                        <div class="mg-client-card__meta">
                            <span>{{ $workout->member?->name ?? 'Sem aluno' }}</span>
                            <span class="mg-client-card__sep">|</span>
                            <span>{{ $workout->workout_date?->format('d/m/Y') ?? 'Template' }}</span>
                            <span class="mg-client-card__sep">|</span>
                            <span>{{ $workout->activities->count() }} exercícios</span>
                        </div>
                        <div class="mg-client-chips">
                            <span class="mg-chip {{ $statusChip }}">{{ $statusLabel }}</span>
                            @if($workout->workout_id)
                                <span class="mg-chip">{{ $workout->workout_id }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="mg-client-card__actions">
                    <i class="ri-arrow-right-s-line mg-client-card__chevron"></i>
                </div>
            </a>
        @empty
            <div class="mg-empty-state">
                <i class="ri-dumbbell-line"></i>
                <p>Nenhuma prescrição de treino encontrada.</p>
                <a href="{{ route('workouts.create') }}" class="mg-btn-primary">Criar treino</a>
            </div>
        @endforelse
    </div>

    @if($workouts->hasPages())
        <div class="mg-pagination">{{ $workouts->links() }}</div>
    @endif
</div>
@endsection
