@extends('layouts.master')

@section('title', 'Treino')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="prime-page-title">{{ $workout->name }}</h1>
        <p class="prime-page-sub">{{ $workout->member->name ?? 'Cliente' }} · {{ $workout->workout_id }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('workouts.edit', $workout) }}" class="btn btn-primary btn-sm">Editar</a>
        <a href="{{ route('workouts.index') }}" class="btn btn-light btn-sm">Voltar</a>
    </div>
</div>

<div class="row g-3">
    @forelse($workout->activities as $activity)
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">{{ $activity->exercise_name }}</h5>
                    <p class="text-muted small mb-2">
                        {{ $activity->sets }} séries × {{ $activity->reps }} reps
                        @if($activity->rest_seconds) · descanso {{ $activity->rest_seconds }}s @endif
                    </p>
                    @if($activity->description && str_contains($activity->description, 'vimeo.com'))
                        <div class="ratio ratio-16x9 mb-2">
                            <iframe src="{{ $activity->description }}?title=0&byline=0&portrait=0" allowfullscreen loading="lazy"></iframe>
                        </div>
                    @endif
                    @if($activity->notes)
                        <p class="small mb-0"><a href="{{ str_replace('Vídeo: ', '', $activity->notes) }}" target="_blank">Abrir vídeo</a></p>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12"><div class="alert alert-info">Nenhum exercício neste treino.</div></div>
    @endforelse
</div>
@endsection
