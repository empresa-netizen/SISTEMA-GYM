@extends('layouts.master')

@section('title', $gymClass->name)

@section('content')
@php
    $initials = collect(explode(' ', $gymClass->name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
    $difficultyMap = [
        'beginner' => ['Iniciante', 'bg-success-subtle text-success'],
        'intermediate' => ['Intermediário', 'bg-warning-subtle text-warning'],
        'advanced' => ['Avançado', 'bg-danger-subtle text-danger'],
    ];
    $difficulty = $difficultyMap[$gymClass->difficulty_level] ?? [ucfirst($gymClass->difficulty_level), 'bg-secondary'];
    $statusMap = [
        'active' => ['Ativa', 'bg-success-subtle text-success'],
        'inactive' => ['Inativa', 'bg-secondary-subtle text-secondary'],
        'cancelled' => ['Cancelada', 'bg-danger-subtle text-danger'],
    ];
    $status = $statusMap[$gymClass->status] ?? [ucfirst($gymClass->status), 'bg-secondary'];
    $dayLabels = [
        'monday' => 'Segunda-feira',
        'tuesday' => 'Terça-feira',
        'wednesday' => 'Quarta-feira',
        'thursday' => 'Quinta-feira',
        'friday' => 'Sexta-feira',
        'saturday' => 'Sábado',
        'sunday' => 'Domingo',
    ];
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="mg-page-title">{{ $gymClass->name }}</h1>
        <p class="mg-page-sub">{{ $gymClass->class_id }} · {{ $gymClass->category->name ?? 'Sem categoria' }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('gym-classes.edit', $gymClass->id) }}" class="btn btn-primary btn-sm"><i class="ri-pencil-line me-1"></i> Editar</a>
        <a href="{{ route('gym-classes.index') }}" class="btn btn-outline-secondary btn-sm"><i class="ri-arrow-left-line"></i></a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="mg-panel text-center">
            @if($gymClass->image)
                <img src="{{ asset('storage/'.$gymClass->image) }}" class="rounded mb-3 img-fluid" alt="">
            @else
                <div class="mg-list-avatar mx-auto mb-3" style="width:4rem;height:4rem;font-size:1.1rem">{{ strtoupper($initials) }}</div>
            @endif
            <span class="badge {{ $status[1] }}">{{ $status[0] }}</span>
            <span class="badge {{ $difficulty[1] }} ms-1">{{ $difficulty[0] }}</span>
            <p class="small text-muted mt-3 mb-0">{{ $gymClass->enrolled_count }} / {{ $gymClass->max_capacity }} matriculados</p>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="mg-panel mb-3" style="height:auto">
            <div class="mg-panel-label mb-3">DETALHES</div>
            <dl class="mg-detail-grid mb-0">
                <dt>Código</dt><dd>{{ $gymClass->class_id }}</dd>
                <dt>Nome</dt><dd>{{ $gymClass->name }}</dd>
                <dt>Categoria</dt><dd>{{ $gymClass->category->name ?? '—' }}</dd>
                <dt>Duração</dt><dd>{{ $gymClass->duration_minutes }} minutos</dd>
                <dt>Capacidade</dt><dd>{{ $gymClass->enrolled_count }} / {{ $gymClass->max_capacity }}</dd>
                <dt>Dificuldade</dt><dd>{{ $difficulty[0] }}</dd>
                <dt>Status</dt><dd>{{ $status[0] }}</dd>
                <dt>Descrição</dt><dd>{{ $gymClass->description ?? '—' }}</dd>
            </dl>
        </div>

        <div class="mg-panel" style="height:auto">
            <div class="mg-panel-label mb-3">HORÁRIOS</div>
            @forelse($gymClass->schedules as $schedule)
                <div class="mg-list-row">
                    <div class="mg-list-body">
                        <div class="mg-list-title">{{ $dayLabels[$schedule->day_of_week] ?? ucfirst($schedule->day_of_week) }}</div>
                        <div class="mg-list-sub">
                            {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                            · {{ $schedule->trainer->name ?? 'Sem treinador' }}
                            · {{ $schedule->room_location ?? 'Salão principal' }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-4">
                    <p class="small mb-0">Nenhum horário cadastrado.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
