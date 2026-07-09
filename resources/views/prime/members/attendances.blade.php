@extends('layouts.master')

@section('title', 'Atendimentos')

@section('content')
<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Atendimentos</h1>
            <div class="prime-clients-counters">
                <span class="prime-clients-counter prime-clients-counter--pending">
                    <i class="ri-time-line"></i>
                    {{ $pending->count() }} pendentes
                </span>
                <span class="prime-clients-counter prime-clients-counter--delivered">
                    <i class="ri-checkbox-circle-fill"></i>
                    {{ $active->count() }} em andamento
                </span>
            </div>
        </div>
        <div class="prime-clients-toolbar__right">
            <a href="{{ route('events.schedule') }}" class="prime-btn-primary">
                <i class="ri-calendar-line"></i> Abrir agenda
            </a>
        </div>
    </div>

    <div class="prime-hub-split">
        <section class="prime-hub-section">
            <div class="prime-hub-section__head">
                <h2 class="prime-section-title mb-0">Pendentes</h2>
                <span class="prime-chip">{{ $pending->count() }}</span>
            </div>
            <div class="prime-client-list">
                @forelse($pending as $event)
                    <div class="prime-list-row">
                        <div class="prime-list-body">
                            <div class="prime-list-title">{{ $event->title }}</div>
                            <div class="prime-list-sub">
                                {{ $event->member?->name ?? 'Sem cliente' }}
                                · {{ $event->start_time->format('d/m/Y H:i') }}
                            </div>
                        </div>
                        <a href="{{ route('events.schedule') }}" class="prime-btn-ghost prime-btn-ghost--sm">Agenda</a>
                    </div>
                @empty
                    <div class="prime-empty-state prime-empty-state--compact">
                        <i class="ri-calendar-todo-line"></i>
                        <p>Nenhum atendimento pendente.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="prime-hub-section">
            <div class="prime-hub-section__head">
                <h2 class="prime-section-title mb-0">Em andamento</h2>
                <span class="prime-chip prime-chip--success">{{ $active->count() }}</span>
            </div>
            <div class="prime-client-list">
                @forelse($active as $event)
                    <div class="prime-list-row">
                        <div class="prime-list-body">
                            <div class="prime-list-title">{{ $event->title }}</div>
                            <div class="prime-list-sub">{{ $event->member?->name ?? 'Sem cliente' }}</div>
                        </div>
                        <span class="prime-chip prime-chip--success"><i class="ri-radio-button-line"></i> Ativo</span>
                    </div>
                @empty
                    <div class="prime-empty-state prime-empty-state--compact">
                        <i class="ri-user-voice-line"></i>
                        <p>Nenhum atendimento ativo agora.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
