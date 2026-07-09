@extends('layouts.master')

@section('title', 'Clientes Ativos')

@section('content')
@php
    $filtersOpen = request()->hasAny(['search_value', 'search', 'plan', 'status']);
@endphp

<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Clientes Ativos</h1>
            <div class="prime-clients-counters">
                <span class="prime-clients-counter prime-clients-counter--pending">
                    <i class="ri-error-warning-fill"></i>
                    {{ $pendingCount ?? 0 }} Pendentes
                </span>
                <span class="prime-clients-counter prime-clients-counter--delivered">
                    <i class="ri-checkbox-circle-fill"></i>
                    {{ $deliveredCount ?? 0 }} Entregues
                </span>
            </div>
        </div>

        <div class="prime-clients-toolbar__right">
            <div class="prime-view-toggle" role="group" aria-label="Visualização">
                <button type="button" class="prime-view-toggle__btn is-active" title="Lista" aria-pressed="true">
                    <i class="ri-list-check-2"></i>
                </button>
                <button type="button" class="prime-view-toggle__btn" title="Kanban" aria-pressed="false" disabled>
                    <i class="ri-layout-column-line"></i>
                </button>
            </div>
            <a href="{{ route('members.engagement') }}" class="prime-btn-ghost">
                <i class="ri-line-chart-line"></i> Análise de engajamento
            </a>
            <button type="button" class="prime-btn-ghost" disabled title="Em breve">
                <i class="ri-notification-3-line"></i> Enviar notificação
            </button>
            <button type="button" class="prime-btn-primary" disabled title="Em breve">
                <i class="ri-download-2-line"></i> Exportar lista
            </button>
            @can('create members')
                <a href="{{ route('members.create') }}" class="prime-btn-primary">
                    <i class="ri-user-add-line"></i> Novo
                </a>
            @endcan
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="prime-clients-filters">
        <button type="button" class="prime-btn-ghost prime-filters-toggle {{ $filtersOpen ? 'is-open' : '' }}" data-bs-toggle="collapse" data-bs-target="#primeClientsFilters" aria-expanded="{{ $filtersOpen ? 'true' : 'false' }}">
            <i class="ri-filter-3-line"></i> Filtros
            <i class="ri-arrow-down-s-line prime-filters-chevron"></i>
        </button>

        <div class="collapse {{ $filtersOpen ? 'show' : '' }}" id="primeClientsFilters">
            <form method="get" action="{{ route('members.index') }}" class="prime-clients-filters__form">
                <div class="prime-clients-filters__grid">
                    <div>
                        <label class="prime-field-label">Buscar</label>
                        <input type="text" name="search_value" value="{{ request('search_value', request('search')) }}" class="prime-field" placeholder="Nome, e-mail, telefone...">
                    </div>
                    <div>
                        <label class="prime-field-label">Plano</label>
                        <select name="plan" class="prime-field">
                            <option value="">Todos os planos</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" @selected(request('plan') == $plan->id)>{{ $plan->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="prime-field-label">Status</label>
                        <select name="status" class="prime-field">
                            <option value="active" @selected(request('status', 'active') === 'active')>Ativo</option>
                            <option value="inactive" @selected(request('status') === 'inactive')>Inativo</option>
                            <option value="expired" @selected(request('status') === 'expired')>Expirado</option>
                            <option value="suspended" @selected(request('status') === 'suspended')>Suspenso</option>
                        </select>
                    </div>
                    <div class="prime-clients-filters__actions">
                        <button type="submit" class="prime-btn-primary">Aplicar</button>
                        <a href="{{ route('members.index') }}" class="prime-btn-ghost">Limpar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="prime-client-list">
        @forelse($members ?? [] as $member)
            @php
                $initials = collect(explode(' ', $member->name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
                $hasAnamnesis = (bool) $member->anamnesis;
                $hasPhotos = $member->photos->isNotEmpty();
                $hasWorkout = $member->workouts->isNotEmpty();
                $hasDiet = $member->dietPrescriptions->isNotEmpty();
                $hasCardio = false;
                $hasPendingFeedback = $member->feedbacks->contains(fn ($f) => ($f->status ?? null) === 'pending');
                $isPending = ! $hasWorkout || $hasPendingFeedback || $member->dietPrescriptions->contains(fn ($d) => ($d->delivery_status ?? null) === 'PENDING');
                $daysLeft = $member->membership_end_date ? (int) now()->startOfDay()->diffInDays($member->membership_end_date->startOfDay(), false) : null;
            @endphp
            <a href="{{ route('members.show', [$member, 'tab' => 'progress']) }}" class="prime-client-card">
                <div class="prime-client-card__main">
                    <div class="prime-client-card__avatar-link">
                        @if($member->photo)
                            <img src="{{ asset('storage/'.$member->photo) }}" alt="" class="prime-client-card__avatar-img">
                        @else
                            <div class="prime-client-card__avatar">{{ strtoupper($initials) }}</div>
                        @endif
                    </div>
                    <div class="prime-client-card__identity">
                        <div class="prime-client-card__name">{{ $member->name }}</div>
                        <div class="prime-client-card__meta">
                            <span>{{ $member->email }}</span>
                            @if($member->phone)
                                <span class="prime-client-card__sep">|</span>
                                <span>{{ $member->phone }}</span>
                            @endif
                        </div>
                        <div class="prime-client-chips">
                            @if($member->membershipPlan)
                                <span class="prime-chip">{{ strtoupper($member->membershipPlan->name) }}</span>
                            @endif
                            <span class="prime-status-badge {{ $hasAnamnesis ? 'is-ok' : 'is-missing' }}">
                                <i class="{{ $hasAnamnesis ? 'ri-checkbox-circle-fill' : 'ri-close-circle-line' }}"></i>
                                {{ $hasAnamnesis ? 'Anamnese' : 'Sem anamnese' }}
                            </span>
                            <span class="prime-status-badge {{ $hasPhotos ? 'is-ok' : 'is-missing' }}">
                                <i class="ri-camera-fill"></i>
                                {{ $hasPhotos ? 'Fotos' : 'Sem fotos' }}
                            </span>
                            <span class="prime-status-badge {{ $hasWorkout ? 'is-ok' : 'is-missing' }}">
                                <i class="ri-dumbbell-fill"></i>
                                {{ $hasWorkout ? 'Treino' : 'Sem treino' }}
                            </span>
                            <span class="prime-status-badge {{ $hasDiet ? 'is-ok' : 'is-missing' }}">
                                <i class="ri-restaurant-fill"></i>
                                {{ $hasDiet ? 'Dieta' : 'Sem dieta' }}
                            </span>
                            <span class="prime-status-badge {{ $hasCardio ? 'is-ok' : 'is-warn' }}">
                                <i class="ri-heart-pulse-fill"></i>
                                {{ $hasCardio ? 'Cardio' : 'Sem cardio' }}
                            </span>
                            @if($daysLeft !== null)
                                @if($daysLeft < 0)
                                    <span class="prime-chip prime-chip--danger"><i class="ri-time-line"></i> Expirado</span>
                                @else
                                    <span class="prime-chip prime-chip--info">{{ $daysLeft }} dias restantes</span>
                                @endif
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
                <i class="ri-group-line"></i>
                <p>Nenhum cliente ativo encontrado.</p>
                @can('create members')
                    <a href="{{ route('members.create') }}" class="prime-btn-primary">Cadastrar cliente</a>
                @endcan
            </div>
        @endforelse
    </div>
</div>
@endsection
