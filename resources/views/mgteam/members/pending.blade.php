@extends('layouts.master')

@section('title', 'Pendências de treino')

@section('content')
<div class="mg-clients-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Pendências de treino</h1>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter mg-clients-counter--pending">
                    <i class="ri-error-warning-fill"></i>
                    {{ $members->total() }} sem treino prescrito
                </span>
            </div>
        </div>
        <div class="mg-clients-toolbar__right">
            <a href="{{ route('members.all') }}" class="mg-btn-ghost">
                <i class="ri-group-line"></i> Todos
            </a>
            <a href="{{ route('members.index') }}" class="mg-btn-ghost">
                <i class="ri-user-follow-line"></i> Ativos
            </a>
            @can('create members')
                <a href="{{ route('members.create') }}" class="mg-btn-primary">
                    <i class="ri-user-add-line"></i> Novo
                </a>
            @endcan
        </div>
    </div>

    <p class="mg-page-sub mb-0">Clientes ativos que ainda não receberam prescrição de treino.</p>

    <div class="mg-client-list">
        @forelse($members as $member)
            @php
                $initials = collect(explode(' ', $member->name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
            @endphp
            <div class="mg-client-card">
                <div class="mg-client-card__main">
                    <a href="{{ route('members.show', $member) }}" class="mg-client-card__avatar-link">
                        @if($member->photo)
                            <img src="{{ asset('storage/'.$member->photo) }}" alt="" class="mg-client-card__avatar-img">
                        @else
                            <div class="mg-client-card__avatar">{{ strtoupper($initials) }}</div>
                        @endif
                    </a>
                    <div class="mg-client-card__identity">
                        <a href="{{ route('members.show', $member) }}" class="mg-client-card__name">{{ $member->name }}</a>
                        <div class="mg-client-card__meta">
                            <span>{{ $member->email }}</span>
                            @if($member->membershipPlan)
                                <span class="mg-client-card__sep">|</span>
                                <span>{{ $member->membershipPlan->name }}</span>
                            @endif
                        </div>
                        <div class="mg-client-chips">
                            <span class="mg-status-badge is-missing">
                                <i class="ri-dumbbell-fill"></i> Sem treino
                            </span>
                            @if($member->membershipPlan)
                                <span class="mg-chip">{{ strtoupper($member->membershipPlan->name) }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="mg-client-card__actions">
                    <a href="{{ route('workouts.create', ['member' => $member->id]) }}" class="mg-btn-primary" style="height:1.85rem;padding:0 0.65rem;font-size:0.75rem">
                        <i class="ri-add-line"></i> Prescrever
                    </a>
                    <a href="{{ route('members.show', $member) }}" class="mg-icon-btn" title="Abrir ficha">
                        <i class="ri-arrow-right-s-line"></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="mg-empty-state">
                <i class="ri-checkbox-circle-line"></i>
                <p>Todos os clientes ativos já possuem treino prescrito.</p>
                <a href="{{ route('members.index') }}" class="mg-btn-ghost">Ver clientes ativos</a>
            </div>
        @endforelse
    </div>

    @if($members->hasPages())
        <div class="mg-pagination">{{ $members->links() }}</div>
    @endif
</div>
@endsection
