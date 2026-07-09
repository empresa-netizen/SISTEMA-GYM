@extends('layouts.master')

@section('title', 'Biblioteca de exercícios')

@section('content')
<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Biblioteca de exercícios</h1>
            <div class="prime-clients-counters">
                <span class="prime-clients-counter">
                    <i class="ri-play-circle-line"></i>
                    {{ $exercises->total() }} vídeos
                </span>
            </div>
        </div>
        <div class="prime-clients-toolbar__right">
            <form method="get" class="prime-inline-search">
                <input type="search" name="q" value="{{ $search }}" class="prime-field" placeholder="Buscar exercício...">
                <button class="prime-btn-primary" type="submit"><i class="ri-search-line"></i></button>
            </form>
        </div>
    </div>

    <div class="prime-exercise-grid">
        @forelse ($exercises as $exercise)
            <div class="prime-exercise-card">
                @if ($exercise->embed_url)
                    <div class="ratio ratio-16x9">
                        <iframe src="{{ $exercise->embed_url }}?title=0&byline=0&portrait=0" allowfullscreen loading="lazy"></iframe>
                    </div>
                @else
                    <div class="prime-exercise-card__placeholder">
                        <i class="ri-play-circle-line"></i>
                    </div>
                @endif
                <div class="prime-exercise-card__body">
                    <h6 class="prime-exercise-card__title">{{ $exercise->name }}</h6>
                    <div class="prime-client-chips">
                        @if ($exercise->duration_seconds)
                            <span class="prime-chip"><i class="ri-time-line"></i> {{ $exercise->duration_seconds }}s</span>
                        @endif
                        @if ($exercise->vimeo_url)
                            <a href="{{ $exercise->vimeo_url }}" target="_blank" rel="noopener" class="prime-chip prime-chip--info text-decoration-none">
                                <i class="ri-external-link-line"></i> Vimeo
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="prime-empty-state" style="grid-column:1/-1">
                <i class="ri-play-list-2-line"></i>
                <p>Nenhum exercício encontrado.</p>
            </div>
        @endforelse
    </div>

    @if($exercises->hasPages())
        <div class="prime-pagination">{{ $exercises->links() }}</div>
    @endif
</div>
@endsection
