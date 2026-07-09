@extends('layouts.master')

@section('title', 'Diário de registros')

@section('content')
@php
    $filtersOpen = request()->hasAny(['q', 'date', 'has_feedback']);
    $tabs = [
        'TRAINING' => ['label' => 'Registros de treino', 'icon' => 'ri-dumbbell-line'],
        'DIET' => ['label' => 'Registros de dieta', 'icon' => 'ri-restaurant-2-line'],
        'CARDIO' => ['label' => 'Registros de cardio', 'icon' => 'ri-heart-pulse-line'],
    ];
    $typeStyles = [
        'TRAINING' => ['label' => 'Treino', 'icon' => 'ri-dumbbell-line', 'chip' => 'prime-chip--info', 'gradient' => 'linear-gradient(135deg,#1d4ed8,#3b82f6)'],
        'DIET' => ['label' => 'Dieta', 'icon' => 'ri-restaurant-2-line', 'chip' => 'prime-chip--success', 'gradient' => 'linear-gradient(135deg,#15803d,#22c55e)'],
        'CARDIO' => ['label' => 'Cardio', 'icon' => 'ri-heart-pulse-line', 'chip' => 'prime-chip--danger', 'gradient' => 'linear-gradient(135deg,#be123c,#fb7185)'],
    ];
@endphp

<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Diário de registros</h1>
            <div class="prime-clients-counters">
                <span class="prime-clients-counter">
                    <i class="ri-book-2-line"></i>
                    {{ array_sum($counts) }} registros
                </span>
            </div>
        </div>
        <div class="prime-clients-toolbar__right">
            <a href="{{ route('members.index') }}" class="prime-btn-ghost">
                <i class="ri-group-line"></i> Clientes ativos
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <p class="prime-page-sub mb-0">Acompanhe registros de treino e dieta enviados pelos clientes.</p>

    <div class="prime-segment-tabs" role="tablist">
        @foreach($tabs as $type => $tabConfig)
            <a href="{{ route('members.logbook', array_merge(request()->except(['page', 'type']), ['type' => $type])) }}"
               class="prime-segment-tab @if($activeType === $type) is-active @endif">
                <i class="{{ $tabConfig['icon'] }}"></i>
                {{ $tabConfig['label'] }}
                <span class="prime-segment-tab__count">{{ $counts[$type] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <div class="prime-clients-filters">
        <button type="button" class="prime-btn-ghost prime-filters-toggle {{ $filtersOpen ? 'is-open' : '' }}" data-bs-toggle="collapse" data-bs-target="#primeLogbookFilters" aria-expanded="{{ $filtersOpen ? 'true' : 'false' }}">
            <i class="ri-filter-3-line"></i> Filtros
            <i class="ri-arrow-down-s-line prime-filters-chevron"></i>
        </button>
        <div class="collapse {{ $filtersOpen ? 'show' : '' }}" id="primeLogbookFilters">
            <form action="{{ route('members.logbook') }}" method="get" class="prime-clients-filters__form">
                <input type="hidden" name="type" value="{{ $activeType }}">
                <div class="prime-clients-filters__grid">
                    <div>
                        <label class="prime-field-label">Buscar</label>
                        <input type="search" name="q" value="{{ request('q') }}" class="prime-field" placeholder="Nome ou e-mail do cliente...">
                    </div>
                    <div>
                        <label class="prime-field-label">Data</label>
                        <input type="date" name="date" value="{{ request('date') }}" class="prime-field">
                    </div>
                    <div>
                        <label class="prime-field-label">Possui feedback</label>
                        <select name="has_feedback" class="prime-field">
                            <option value="">Todos</option>
                            <option value="yes" @selected(request('has_feedback') === 'yes')>Com feedback</option>
                            <option value="no" @selected(request('has_feedback') === 'no')>Sem feedback</option>
                        </select>
                    </div>
                    <div class="prime-clients-filters__actions">
                        <button type="submit" class="prime-btn-primary"><i class="ri-search-line"></i> Aplicar</button>
                        <a href="{{ route('members.logbook', ['type' => $activeType]) }}" class="prime-btn-ghost">Limpar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="prime-client-list">
        @forelse($entries as $entry)
            @php
                $style = $typeStyles[$entry->type] ?? $typeStyles['TRAINING'];
                $name = $entry->member?->name ?? 'Cliente removido';
                $initials = collect(explode(' ', $name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
                $hasFeedback = $entry->member?->feedbacks?->isNotEmpty() ?? false;
            @endphp
            <div class="prime-client-card">
                <div class="prime-client-card__main">
                    <div class="prime-client-card__avatar" style="background:{{ $style['gradient'] }}">
                        <i class="{{ $style['icon'] }}"></i>
                    </div>
                    <div class="prime-client-card__identity">
                        <div class="prime-client-card__name">{{ $entry->title ?: $style['label'] }}</div>
                        <div class="prime-client-card__meta">
                            <span>{{ $name }}</span>
                            @if($entry->member?->email)
                                <span class="prime-client-card__sep">|</span>
                                <span>{{ $entry->member->email }}</span>
                            @endif
                            <span class="prime-client-card__sep">|</span>
                            <span>{{ $entry->logged_at?->format('d/m/Y') ?? $entry->created_at?->format('d/m/Y') }}</span>
                        </div>
                        <div class="prime-client-chips">
                            <span class="prime-chip {{ $style['chip'] }}">{{ $style['label'] }}</span>
                            @if($entry->rating)
                                <span class="prime-chip prime-chip--warn">{{ $entry->rating }} ★</span>
                            @endif
                            <span class="prime-chip {{ $hasFeedback ? 'prime-chip--success' : '' }}">
                                {{ $hasFeedback ? 'Com feedback' : 'Sem feedback' }}
                            </span>
                        </div>
                        <p class="prime-hub-excerpt">{{ Str::limit($entry->comment ?: 'Sem observações no registro.', 150) }}</p>
                    </div>
                </div>
                <div class="prime-client-card__actions">
                    @if($entry->member)
                        <a href="{{ route('members.show', ['member' => $entry->member, 'tab' => 'logbook']) }}" class="prime-btn-ghost prime-btn-ghost--sm">
                            Visualizar
                        </a>
                    @endif
                    <form method="POST" action="{{ route('members.logbooks.destroy', $entry) }}" class="prime-inline-form" onsubmit="return confirm('Excluir este registro do diário?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="prime-btn-ghost prime-btn-ghost--sm text-danger">Excluir</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="prime-empty-state">
                <i class="ri-book-2-line"></i>
                <p>Nenhum registro encontrado para esta aba.</p>
            </div>
        @endforelse
    </div>

    @if($entries->hasPages())
        <div class="prime-pagination">{{ $entries->links() }}</div>
    @endif
</div>
@endsection
