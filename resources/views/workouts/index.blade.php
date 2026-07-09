@extends('layouts.master')

@section('title', 'Treinos')

@section('content')
@php
    $filtersOpen = request()->hasAny(['search_value', 'member', 'status']);
    $statusChips = [
        'active' => ['Ativo', 'prime-chip--success'],
        'completed' => ['Concluído', 'prime-chip--info'],
        'cancelled' => ['Cancelado', 'prime-chip--danger'],
    ];
@endphp

<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Prescrições de treino</h1>
            <div class="prime-clients-counters">
                <span class="prime-clients-counter">
                    <i class="ri-dumbbell-line"></i>
                    {{ $workouts->total() }} treinos
                </span>
            </div>
        </div>
        <div class="prime-clients-toolbar__right">
            <a href="{{ route('workouts.today') }}" class="prime-btn-ghost">
                <i class="ri-calendar-check-line"></i> Treinos de hoje
            </a>
            <a href="{{ route('workouts.create') }}" class="prime-btn-primary">
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

    <div class="prime-clients-filters">
        <button type="button" class="prime-btn-ghost prime-filters-toggle {{ $filtersOpen ? 'is-open' : '' }}" data-bs-toggle="collapse" data-bs-target="#primeWorkoutsFilters" aria-expanded="{{ $filtersOpen ? 'true' : 'false' }}">
            <i class="ri-filter-3-line"></i> Filtros
            <i class="ri-arrow-down-s-line prime-filters-chevron"></i>
        </button>
        <div class="collapse {{ $filtersOpen ? 'show' : '' }}" id="primeWorkoutsFilters">
            <form action="{{ route('workouts.index') }}" method="get" class="prime-clients-filters__form">
                <div class="prime-clients-filters__grid">
                    <div>
                        <label class="prime-field-label">Buscar</label>
                        <input type="text" name="search_value" value="{{ request('search_value') }}" class="prime-field" placeholder="Nome ou ID do treino...">
                    </div>
                    <div>
                        <label class="prime-field-label">Cliente</label>
                        <select name="member" class="prime-field">
                            <option value="">Todos os clientes</option>
                            @foreach($members as $member)
                                <option value="{{ $member->id }}" @selected(request('member') == $member->id)>{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="prime-field-label">Status</label>
                        <select name="status" class="prime-field">
                            <option value="">Todos</option>
                            <option value="active" @selected(request('status') === 'active')>Ativo</option>
                            <option value="completed" @selected(request('status') === 'completed')>Concluído</option>
                            <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelado</option>
                        </select>
                    </div>
                    <div class="prime-clients-filters__actions">
                        <button type="submit" class="prime-btn-primary">Aplicar</button>
                        <a href="{{ route('workouts.index') }}" class="prime-btn-ghost">Limpar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="prime-client-list">
        @forelse($workouts as $workout)
            @php
                [$statusLabel, $statusChip] = $statusChips[$workout->status] ?? [ucfirst($workout->status), ''];
            @endphp
            <a href="{{ route('workouts.show', $workout) }}" class="prime-client-card">
                <div class="prime-client-card__main">
                    <div class="prime-client-card__avatar" style="background:linear-gradient(135deg,#1d4ed8,#3b82f6)">
                        <i class="ri-dumbbell-line"></i>
                    </div>
                    <div class="prime-client-card__identity">
                        <div class="prime-client-card__name">{{ $workout->name }}</div>
                        <div class="prime-client-card__meta">
                            <span>{{ $workout->member?->name ?? 'Sem aluno' }}</span>
                            <span class="prime-client-card__sep">|</span>
                            <span>{{ $workout->workout_date?->format('d/m/Y') ?? 'Template' }}</span>
                            <span class="prime-client-card__sep">|</span>
                            <span>{{ $workout->activities->count() }} exercícios</span>
                        </div>
                        <div class="prime-client-chips">
                            <span class="prime-chip {{ $statusChip }}">{{ $statusLabel }}</span>
                            @if($workout->workout_id)
                                <span class="prime-chip">{{ $workout->workout_id }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="prime-client-card__actions">
                    <i class="ri-arrow-right-s-line prime-client-card__chevron"></i>
                </div>
            </a>
        @empty
            <div class="prime-empty-state">
                <i class="ri-dumbbell-line"></i>
                <p>Nenhuma prescrição de treino encontrada.</p>
                <a href="{{ route('workouts.create') }}" class="prime-btn-primary">Criar treino</a>
            </div>
        @endforelse
    </div>

    @if($workouts->hasPages())
        <div class="prime-pagination">{{ $workouts->links() }}</div>
    @endif
</div>
@endsection
