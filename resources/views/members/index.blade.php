@extends('layouts.master')

@section('title', 'Clientes Ativos')

@section('content')
@php
    $filtersOpen = true;
@endphp

@push('styles')
<style>
    .mg-clients-page {
        display: flex;
        flex-direction: column;
        gap: 1.05rem;
    }

    .mg-clients-toolbar {
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 0.4rem;
    }

    .mg-clients-toolbar__left {
        display: flex;
        flex-direction: column;
        gap: 0.68rem;
    }

    .mg-clients-toolbar__right {
        gap: 0.75rem;
    }

    .mg-clients-counters {
        display: inline-flex;
        flex-wrap: wrap;
        gap: 1.05rem;
    }

    .mg-clients-counter {
        color: #9EAAC0;
        font-size: 0.94rem;
        font-weight: 820;
    }

    .mg-clients-counter i {
        margin-right: 0.45rem;
    }

    .mg-clients-counter--pending i {
        color: #F46F68;
    }

    .mg-clients-counter--delivered i {
        color: #6AB7FF;
    }

    .mg-view-toggle {
        display: inline-flex;
        padding: 0.25rem;
        border: 1px solid rgba(87, 111, 148, 0.28);
        border-radius: 0.72rem;
        background: #172235;
    }

    .mg-view-toggle__btn {
        width: 2.58rem;
        height: 2.58rem;
        border: 0;
        border-radius: 0.55rem;
        background: transparent;
        color: #9EAAC0;
        font-size: 1.18rem;
    }

    .mg-view-toggle__btn.is-active {
        background: #0B1424 !important;
        color: #DDEBFF !important;
    }

    .mg-clients-filters {
        padding: 1.08rem;
        border-radius: 0.84rem;
        background: #111A2B !important;
    }

    .mg-filters-toggle {
        width: auto;
        min-height: 2.65rem;
        padding-inline: 0.95rem;
    }

    .mg-clients-filters__form {
        padding-top: 1.15rem;
    }

    .mg-clients-filters__grid {
        display: grid;
        grid-template-columns: minmax(18rem, 1.35fr) repeat(2, minmax(11rem, 0.65fr)) auto;
        gap: 0.85rem;
        align-items: end;
    }

    .mg-clients-filters__actions {
        display: flex;
        gap: 0.55rem;
    }

    .mg-client-list {
        display: flex;
        flex-direction: column;
        gap: 0.72rem;
    }

    .mg-client-card {
        min-height: 5.85rem;
        padding: 0.82rem 1rem;
        border-radius: 0.88rem;
        background: #111A2B !important;
    }

    .mg-client-card__main {
        gap: 0.78rem;
    }

    .mg-client-card__avatar,
    .mg-client-card__avatar-img {
        width: 2.62rem;
        height: 2.62rem;
        border-radius: 999px;
    }

    .mg-client-card__avatar {
        background: #0C356B !important;
        color: #9ECFFF !important;
        font-size: 0.78rem;
        font-weight: 950;
    }

    .mg-client-card__name {
        color: #F6F8FC;
        font-size: 0.95rem;
        font-weight: 900;
    }

    .mg-client-card__meta {
        color: #8F9DB3;
        font-size: 0.78rem;
        font-weight: 720;
    }

    .mg-client-chips {
        gap: 0.35rem;
        margin-top: 0.42rem;
    }

    .mg-status-badge,
    .mg-chip {
        min-height: 1.55rem;
        padding: 0 0.52rem;
        border-radius: 999px;
        font-size: 0.68rem;
        letter-spacing: 0;
    }

    .mg-client-card__actions {
        color: #7C8AA2;
    }

    @media (max-width: 1180px) {
        .mg-clients-filters__grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 720px) {
        .mg-clients-toolbar,
        .mg-clients-toolbar__right,
        .mg-clients-filters__grid {
            grid-template-columns: 1fr;
        }

        .mg-clients-toolbar__right {
            width: 100%;
        }
    }
</style>
@endpush

<div class="mg-clients-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Clientes Ativos</h1>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter mg-clients-counter--pending">
                    <i class="ri-error-warning-fill"></i>
                    {{ $pendingCount ?? 0 }} Pendentes
                </span>
                <span class="mg-clients-counter mg-clients-counter--delivered">
                    <i class="ri-checkbox-circle-fill"></i>
                    {{ $deliveredCount ?? 0 }} Entregues
                </span>
            </div>
        </div>

        <div class="mg-clients-toolbar__right">
            <div class="mg-view-toggle" role="group" aria-label="Visualização">
                <button type="button" class="mg-view-toggle__btn is-active" title="Lista" aria-pressed="true">
                    <i class="ri-list-check-2"></i>
                </button>
                <button type="button" class="mg-view-toggle__btn" title="Kanban" aria-pressed="false" disabled>
                    <i class="ri-layout-column-line"></i>
                </button>
            </div>
            <a href="{{ route('members.engagement') }}" class="mg-btn-ghost">
                <i class="ri-line-chart-line"></i> Análise de engajamento
            </a>
            <button type="button" class="mg-btn-ghost" disabled title="Em breve">
                <i class="ri-notification-3-line"></i> Enviar notificação
            </button>
            <button type="button" class="mg-btn-primary" disabled title="Em breve">
                <i class="ri-download-2-line"></i> Exportar lista
            </button>
            @can('create members')
                <a href="{{ route('members.create') }}" class="mg-btn-primary">
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

    <div class="mg-clients-filters">
        <button type="button" class="mg-btn-ghost mg-filters-toggle {{ $filtersOpen ? 'is-open' : '' }}" data-bs-toggle="collapse" data-bs-target="#mgClientsFilters" aria-expanded="{{ $filtersOpen ? 'true' : 'false' }}">
            <i class="ri-filter-3-line"></i> Filtros
            <i class="ri-arrow-down-s-line mg-filters-chevron"></i>
        </button>

        <div class="collapse {{ $filtersOpen ? 'show' : '' }}" id="mgClientsFilters">
            <form method="get" action="{{ route('members.index') }}" class="mg-clients-filters__form">
                <div class="mg-clients-filters__grid">
                    <div>
                        <label class="mg-field-label">Buscar</label>
                        <input type="text" name="search_value" value="{{ request('search_value', request('search')) }}" class="mg-field" placeholder="Nome, e-mail, telefone...">
                    </div>
                    <div>
                        <label class="mg-field-label">Plano</label>
                        <select name="plan" class="mg-field">
                            <option value="">Todos os planos</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" @selected(request('plan') == $plan->id)>{{ $plan->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mg-field-label">Status</label>
                        <select name="status" class="mg-field">
                            <option value="active" @selected(request('status', 'active') === 'active')>Ativo</option>
                            <option value="inactive" @selected(request('status') === 'inactive')>Inativo</option>
                            <option value="expired" @selected(request('status') === 'expired')>Expirado</option>
                            <option value="suspended" @selected(request('status') === 'suspended')>Suspenso</option>
                        </select>
                    </div>
                    <div class="mg-clients-filters__actions">
                        <button type="submit" class="mg-btn-primary">Aplicar</button>
                        <a href="{{ route('members.index') }}" class="mg-btn-ghost">Limpar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="mg-client-list">
        @forelse($members ?? [] as $member)
            @php
                $initials = collect(explode(' ', $member->name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
                $hasAnamnesis = (bool) $member->anamnesis;
                $hasPhotos = $member->photos->isNotEmpty();
                $hasWorkout = $member->workouts->isNotEmpty();
                $hasDiet = $member->dietPrescriptions->isNotEmpty();
                $hasCardio = ($member->cardioPlans ?? collect())->isNotEmpty();
                $hasPendingFeedback = $member->feedbacks->contains(fn ($f) => ($f->status ?? null) === 'pending');
                $isPending = ! $hasWorkout || $hasPendingFeedback || $member->dietPrescriptions->contains(fn ($d) => ($d->delivery_status ?? null) === 'PENDING');
                $daysLeft = $member->membership_end_date ? (int) now()->startOfDay()->diffInDays($member->membership_end_date->startOfDay(), false) : null;
            @endphp
            <div class="mg-client-card">
                <a href="{{ route('members.show', [$member, 'tab' => 'progress']) }}" class="mg-client-card__main text-decoration-none text-reset">
                    <div class="mg-client-card__avatar-link">
                        @if($member->photo)
                            <img src="{{ asset('storage/'.$member->photo) }}" alt="" class="mg-client-card__avatar-img">
                        @else
                            <div class="mg-client-card__avatar">{{ strtoupper($initials) }}</div>
                        @endif
                    </div>
                    <div class="mg-client-card__identity">
                        <div class="mg-client-card__name">{{ $member->name }}</div>
                        <div class="mg-client-card__meta">
                            <span>{{ $member->email }}</span>
                            @if($member->phone)
                                <span class="mg-client-card__sep">|</span>
                                <span>{{ $member->phone }}</span>
                            @endif
                        </div>
                        <div class="mg-client-chips">
                            @if($member->membershipPlan)
                                <span class="mg-chip">{{ strtoupper($member->membershipPlan->name) }}</span>
                            @endif
                            <a href="{{ route('members.show', [$member, 'tab' => 'anamnesis']) }}" class="mg-status-badge {{ $hasAnamnesis ? 'is-ok' : 'is-missing' }}" onclick="event.stopPropagation()">
                                <i class="{{ $hasAnamnesis ? 'ri-checkbox-circle-fill' : 'ri-close-circle-line' }}"></i>
                                {{ $hasAnamnesis ? 'Anamnese' : 'Sem anamnese' }}
                            </a>
                            <a href="{{ route('members.show', [$member, 'tab' => 'photos']) }}" class="mg-status-badge {{ $hasPhotos ? 'is-ok' : 'is-missing' }}" onclick="event.stopPropagation()">
                                <i class="ri-camera-fill"></i>
                                {{ $hasPhotos ? 'Fotos' : 'Sem fotos' }}
                            </a>
                            <a href="{{ route('members.show', [$member, 'tab' => 'workout']) }}" class="mg-status-badge {{ $hasWorkout ? 'is-ok' : 'is-missing' }}" onclick="event.stopPropagation()">
                                <i class="ri-dumbbell-fill"></i>
                                {{ $hasWorkout ? 'Treino' : 'Sem treino' }}
                            </a>
                            <a href="{{ route('members.show', [$member, 'tab' => 'diet']) }}" class="mg-status-badge {{ $hasDiet ? 'is-ok' : 'is-missing' }}" onclick="event.stopPropagation()">
                                <i class="ri-restaurant-fill"></i>
                                {{ $hasDiet ? 'Dieta' : 'Sem dieta' }}
                            </a>
                            <a href="{{ route('members.show', [$member, 'tab' => 'cardio']) }}" class="mg-status-badge {{ $hasCardio ? 'is-ok' : 'is-warn' }}" onclick="event.stopPropagation()">
                                <i class="ri-heart-pulse-fill"></i>
                                {{ $hasCardio ? 'Cardio' : 'Sem cardio' }}
                            </a>
                            @if($daysLeft !== null)
                                @if($daysLeft < 0)
                                    <span class="mg-chip mg-chip--danger"><i class="ri-time-line"></i> Expirado</span>
                                @else
                                    <span class="mg-chip mg-chip--info">{{ $daysLeft }} dias restantes</span>
                                @endif
                            @endif
                        </div>
                    </div>
                </a>
                <a href="{{ route('members.show', [$member, 'tab' => 'progress']) }}" class="mg-client-card__actions text-decoration-none" aria-label="Abrir cliente">
                    <i class="ri-arrow-right-s-line mg-client-card__chevron"></i>
                </a>
            </div>
        @empty
            <div class="mg-empty-state">
                <i class="ri-group-line"></i>
                <p>Nenhum cliente ativo encontrado.</p>
                @can('create members')
                    <a href="{{ route('members.create') }}" class="mg-btn-primary">Cadastrar cliente</a>
                @endcan
            </div>
        @endforelse
    </div>
</div>
@endsection
