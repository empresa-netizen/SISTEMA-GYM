@extends('layouts.master')

@section('title', 'Feedbacks')

@section('content')
@php
    $filtersOpen = request()->hasAny(['q', 'date', 'rating']);
    $statusTabs = [
        'pending' => ['label' => 'Pendentes', 'icon' => 'ri-error-warning-line'],
        'viewed' => ['label' => 'Visualizados', 'icon' => 'ri-eye-line'],
        'resolved' => ['label' => 'Resolvidos', 'icon' => 'ri-checkbox-circle-line'],
        'all' => ['label' => 'Todos', 'icon' => 'ri-inbox-archive-line'],
    ];
    $statusChips = [
        'pending' => ['Pendente', 'mg-chip--warn'],
        'viewed' => ['Visualizado', 'mg-chip--info'],
        'resolved' => ['Resolvido', 'mg-chip--success'],
    ];
    $contextLabels = [
        'workout' => ['Treino', 'ri-run-line'],
        'exercise' => ['Exercício', 'ri-weight-line'],
        'diet' => ['Dieta', 'ri-restaurant-2-line'],
        'meal' => ['Refeição', 'ri-bowl-line'],
        'general' => ['Geral', 'ri-chat-quote-line'],
    ];
@endphp

<div class="mg-clients-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Feedbacks</h1>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter mg-clients-counter--pending">
                    <i class="ri-error-warning-fill"></i>
                    {{ $counts['pending'] ?? 0 }} pendentes
                </span>
                <span class="mg-clients-counter">
                    <i class="ri-eye-line"></i>
                    {{ $counts['viewed'] ?? 0 }} visualizados
                </span>
                <span class="mg-clients-counter mg-clients-counter--delivered">
                    <i class="ri-checkbox-circle-fill"></i>
                    {{ $counts['resolved'] ?? 0 }} resolvidos
                </span>
            </div>
        </div>
        <div class="mg-clients-toolbar__right">
            <a href="{{ route('members.index') }}" class="mg-btn-ghost">
                <i class="ri-group-line"></i> Clientes ativos
            </a>
        </div>
    </div>

    <p class="mg-page-sub mb-0">Centralize retornos enviados pelos clientes e acompanhe o status de atendimento.</p>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="mg-segment-tabs" role="tablist">
        @foreach($statusTabs as $key => $tabConfig)
            <a href="{{ route('feedbacks.index', array_merge(request()->except(['page', 'tab']), ['tab' => $key])) }}"
               class="mg-segment-tab @if($tab === $key) is-active @endif">
                <i class="{{ $tabConfig['icon'] }}"></i>
                {{ $tabConfig['label'] }}
                <span class="mg-segment-tab__count">{{ $counts[$key] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <div class="mg-clients-filters">
        <button type="button" class="mg-btn-ghost mg-filters-toggle {{ $filtersOpen ? 'is-open' : '' }}" data-bs-toggle="collapse" data-bs-target="#mgFeedbackFilters" aria-expanded="{{ $filtersOpen ? 'true' : 'false' }}">
            <i class="ri-filter-3-line"></i> Filtros
            <i class="ri-arrow-down-s-line mg-filters-chevron"></i>
        </button>
        <div class="collapse {{ $filtersOpen ? 'show' : '' }}" id="mgFeedbackFilters">
            <form action="{{ route('feedbacks.index') }}" method="get" class="mg-clients-filters__form">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="mg-clients-filters__grid">
                    <div>
                        <label class="mg-field-label">Buscar</label>
                        <input type="search" name="q" value="{{ request('q') }}" class="mg-field" placeholder="Nome, e-mail ou mensagem...">
                    </div>
                    <div>
                        <label class="mg-field-label">Data</label>
                        <input type="date" name="date" value="{{ request('date') }}" class="mg-field">
                    </div>
                    <div>
                        <label class="mg-field-label">Nota</label>
                        <select name="rating" class="mg-field">
                            <option value="">Todas</option>
                            @for($rating = 5; $rating >= 1; $rating--)
                                <option value="{{ $rating }}" @selected((string) request('rating') === (string) $rating)>{{ $rating }} estrelas</option>
                            @endfor
                        </select>
                    </div>
                    <div class="mg-clients-filters__actions">
                        <button type="submit" class="mg-btn-primary"><i class="ri-search-line"></i> Aplicar</button>
                        <a href="{{ route('feedbacks.index', ['tab' => $tab]) }}" class="mg-btn-ghost">Limpar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="mg-client-list">
        @forelse($feedbacks as $feedback)
            @php
                $name = $feedback->member?->name ?? 'Cliente removido';
                $initials = collect(explode(' ', $name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
                [$statusLabel, $statusChip] = $statusChips[$feedback->status] ?? [ucfirst($feedback->status ?? 'novo'), ''];
            @endphp
            <div class="mg-client-card mg-client-card--stack">
                <div class="mg-client-card__main">
                    <div class="mg-client-card__avatar">{{ strtoupper($initials ?: '?') }}</div>
                    <div class="mg-client-card__identity">
                        <div class="mg-client-card__name">{{ $name }}</div>
                        <div class="mg-client-card__meta">
                            <span>{{ $feedback->created_at?->format('d/m/Y H:i') }}</span>
                            @if($feedback->member?->email)
                                <span class="mg-client-card__sep">|</span>
                                <span>{{ $feedback->member->email }}</span>
                            @endif
                            @if($feedback->rating)
                                <span class="mg-client-card__sep">|</span>
                                <span class="mg-chip mg-chip--warn">{{ $feedback->rating }} ★</span>
                            @endif
                        </div>
                        <div class="mg-client-chips">
                            <span class="mg-chip {{ $statusChip }}">{{ $statusLabel }}</span>
                            @if($feedback->context_type)
                                @php
                                    [$contextLabel, $contextIcon] = $contextLabels[$feedback->context_type] ?? [Str::title($feedback->context_type), 'ri-link'];
                                @endphp
                                <span class="mg-chip mg-chip--info">
                                    <i class="{{ $contextIcon }}"></i>
                                    {{ $contextLabel }}@if($feedback->context_id) #{{ $feedback->context_id }}@endif
                                </span>
                            @endif
                            @if($feedback->photo_path)
                                <span class="mg-chip"><i class="ri-image-line"></i> Foto anexada</span>
                            @endif
                        </div>
                        <p class="mg-hub-excerpt">{{ Str::limit($feedback->message ?: 'Sem mensagem.', 170) }}</p>
                    </div>
                </div>
                <div class="mg-client-card__actions mg-feedback-actions">
                    <form method="POST" action="{{ route('feedbacks.update', $feedback) }}" class="mg-inline-form">
                        @csrf
                        @method('PATCH')
                        <select name="status" class="mg-field mg-field--sm" onchange="this.form.submit()">
                            <option value="pending" @selected($feedback->status === 'pending')>Pendente</option>
                            <option value="viewed" @selected($feedback->status === 'viewed')>Visualizado</option>
                            <option value="resolved" @selected($feedback->status === 'resolved')>Resolvido</option>
                        </select>
                    </form>
                    @if($feedback->member)
                        <a href="{{ route('members.show', ['member' => $feedback->member, 'tab' => 'feedbacks']) }}" class="mg-btn-ghost mg-btn-ghost--sm">
                            Visualizar
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="mg-empty-state">
                <i class="ri-chat-quote-line"></i>
                <p>Nenhum feedback encontrado nesta seleção.</p>
            </div>
        @endforelse
    </div>

    @if($feedbacks->hasPages())
        <div class="mg-pagination">{{ $feedbacks->links() }}</div>
    @endif
</div>
@endsection
