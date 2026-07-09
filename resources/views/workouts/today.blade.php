@extends('layouts.master')

@section('title', 'Treinos de hoje')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="prime-page-title">Treinos de hoje</h1>
        <p class="prime-page-sub">{{ now()->translatedFormat('l, d \d\e F \d\e Y') }}</p>
    </div>
    <a href="{{ route('workouts.index') }}" class="btn btn-outline-secondary btn-sm"><i class="ri-arrow-left-line me-1"></i> Voltar</a>
</div>

<div class="prime-panel">
    @forelse($workouts as $workout)
    <div class="d-flex flex-wrap justify-content-between align-items-center py-3 border-bottom border-secondary border-opacity-25 gap-2">
        <div>
            <strong>{{ $workout->name }}</strong>
            <div class="text-muted small">
                {{ $workout->member?->name ?? 'Sem cliente' }}
                · {{ $workout->activities->count() }} exercícios
            </div>
            <span class="badge bg-primary">{{ ucfirst($workout->status) }}</span>
        </div>
        <a href="{{ route('workouts.show', $workout) }}" class="btn btn-sm btn-outline-primary">Abrir</a>
    </div>
    @empty
    <p class="text-muted mb-3">Nenhum treino agendado para hoje.</p>
    <a href="{{ route('workouts.create') }}" class="btn btn-primary btn-sm">Nova prescrição</a>
    @endforelse
</div>
@endsection
