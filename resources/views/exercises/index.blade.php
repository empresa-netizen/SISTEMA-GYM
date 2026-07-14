@extends('layouts.master')

@section('title', 'Biblioteca de exercícios')

@section('content')
<div class="mg-clients-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Biblioteca de exercícios</h1>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter">
                    <i class="ri-play-circle-line"></i>
                    {{ $exercises->total() }} vídeos
                </span>
            </div>
        </div>
        <div class="mg-clients-toolbar__right">
            <form method="get" class="mg-inline-search">
                <input type="search" name="q" value="{{ $search }}" class="mg-field" placeholder="Buscar exercício...">
                <button class="mg-btn-primary" type="submit"><i class="ri-search-line"></i></button>
            </form>
        </div>
    </div>

    <div class="mg-exercise-grid">
        @forelse ($exercises as $exercise)
            <div class="mg-exercise-card">
                @if ($exercise->embed_url)
                    <div class="ratio ratio-16x9">
                        <iframe src="{{ $exercise->embed_url }}?title=0&byline=0&portrait=0" allowfullscreen loading="lazy"></iframe>
                    </div>
                @else
                    <div class="mg-exercise-card__placeholder">
                        <i class="ri-play-circle-line"></i>
                    </div>
                @endif
                <div class="mg-exercise-card__body">
                    <h6 class="mg-exercise-card__title">{{ $exercise->name }}</h6>
                    <div class="mg-client-chips">
                        @if ($exercise->duration_seconds)
                            <span class="mg-chip"><i class="ri-time-line"></i> {{ $exercise->duration_seconds }}s</span>
                        @endif
                        @if ($exercise->vimeo_url)
                            <a href="{{ $exercise->vimeo_url }}" target="_blank" rel="noopener" class="mg-chip mg-chip--info text-decoration-none">
                                <i class="ri-external-link-line"></i> Vimeo
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="mg-empty-state" style="grid-column:1/-1">
                <i class="ri-play-list-2-line"></i>
                <p>Nenhum exercício encontrado.</p>
            </div>
        @endforelse
    </div>

    @if($exercises->hasPages())
        <div class="mg-pagination">{{ $exercises->links() }}</div>
    @endif
</div>
@endsection
