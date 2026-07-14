@extends('layouts.master')

@section('title', 'Atendimentos')

@section('content')
<div class="mg-clients-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Atendimentos</h1>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter mg-clients-counter--pending">
                    <i class="ri-time-line"></i>
                    {{ $pending->count() }} pendentes
                </span>
                <span class="mg-clients-counter mg-clients-counter--delivered">
                    <i class="ri-checkbox-circle-fill"></i>
                    {{ $active->count() }} em andamento
                </span>
            </div>
        </div>
        <div class="mg-clients-toolbar__right">
            <a href="{{ route('events.schedule') }}" class="mg-btn-primary">
                <i class="ri-calendar-line"></i> Abrir agenda
            </a>
        </div>
    </div>

    <div class="mg-hub-split">
        <section class="mg-hub-section">
            <div class="mg-hub-section__head">
                <h2 class="mg-section-title mb-0">Pendentes</h2>
                <span class="mg-chip">{{ $pending->count() }}</span>
            </div>
            <div class="mg-client-list">
                @forelse($pending as $event)
                    <div class="mg-list-row">
                        <div class="mg-list-body">
                            <div class="mg-list-title">{{ $event->title }}</div>
                            <div class="mg-list-sub">
                                {{ $event->member?->name ?? 'Sem cliente' }}
                                · {{ $event->start_time->format('d/m/Y H:i') }}
                            </div>
                        </div>
                        <a href="{{ route('events.schedule') }}" class="mg-btn-ghost mg-btn-ghost--sm">Agenda</a>
                    </div>
                @empty
                    <div class="mg-empty-state mg-empty-state--compact">
                        <i class="ri-calendar-todo-line"></i>
                        <p>Nenhum atendimento pendente.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="mg-hub-section">
            <div class="mg-hub-section__head">
                <h2 class="mg-section-title mb-0">Em andamento</h2>
                <span class="mg-chip mg-chip--success">{{ $active->count() }}</span>
            </div>
            <div class="mg-client-list">
                @forelse($active as $event)
                    <div class="mg-list-row">
                        <div class="mg-list-body">
                            <div class="mg-list-title">{{ $event->title }}</div>
                            <div class="mg-list-sub">{{ $event->member?->name ?? 'Sem cliente' }}</div>
                        </div>
                        <span class="mg-chip mg-chip--success"><i class="ri-radio-button-line"></i> Ativo</span>
                    </div>
                @empty
                    <div class="mg-empty-state mg-empty-state--compact">
                        <i class="ri-user-voice-line"></i>
                        <p>Nenhum atendimento ativo agora.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
