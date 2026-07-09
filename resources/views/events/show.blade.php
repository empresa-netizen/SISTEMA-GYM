@extends('layouts.master')

@section('title', $event->title)

@section('content')
@php
    $initials = collect(explode(' ', $event->title))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
    $statusMap = [
        'scheduled' => ['Agendado', 'bg-info-subtle text-info'],
        'ongoing' => ['Em andamento', 'bg-success-subtle text-success'],
        'completed' => ['Concluído', 'bg-secondary-subtle text-secondary'],
        'cancelled' => ['Cancelado', 'bg-danger-subtle text-danger'],
    ];
    $status = $statusMap[$event->status] ?? [ucfirst($event->status), 'bg-secondary'];
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="prime-page-title">{{ $event->title }}</h1>
        <p class="prime-page-sub">{{ $event->location ?? 'Sem local definido' }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('events.edit', $event) }}" class="btn btn-primary btn-sm"><i class="ri-pencil-line me-1"></i> Editar</a>
        <a href="{{ route('events.schedule') }}" class="btn btn-outline-secondary btn-sm"><i class="ri-arrow-left-line"></i></a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="prime-panel text-center">
            @if($event->image)
                <img src="{{ asset('storage/'.$event->image) }}" class="rounded mb-3 img-fluid" alt="">
            @else
                <div class="prime-list-avatar mx-auto mb-3" style="width:4rem;height:4rem;font-size:1.1rem">{{ strtoupper($initials) }}</div>
            @endif
            <span class="badge {{ $status[1] }}">{{ $status[0] }}</span>
            @if($event->member)
                <p class="small text-muted mt-3 mb-0">Cliente: <a href="{{ route('members.show', $event->member) }}">{{ $event->member->name }}</a></p>
            @endif
        </div>
    </div>
    <div class="col-lg-8">
        <div class="prime-panel">
            <dl class="prime-detail-grid mb-0">
                <dt>Início</dt><dd>{{ $event->start_time->format('d/m/Y H:i') }}</dd>
                <dt>Término</dt><dd>{{ $event->end_time->format('d/m/Y H:i') }}</dd>
                <dt>Participantes</dt><dd>{{ $event->registered_count }} / {{ $event->max_participants ?? 'Ilimitado' }}</dd>
                <dt>Descrição</dt><dd>{{ $event->description ?? '—' }}</dd>
            </dl>
        </div>
    </div>
</div>
@endsection
