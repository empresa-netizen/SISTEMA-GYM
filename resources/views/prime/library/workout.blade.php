@extends('layouts.master')

@section('title', 'Treinamento')

@section('content')
@php
    $actionCards = [
        [
            'title' => 'Meus exercícios',
            'desc' => 'Organize a biblioteca local de movimentos, vídeos e instruções.',
            'icon' => 'ri-play-circle-line',
            'route' => 'exercises.index',
            'gradient' => 'linear-gradient(135deg,#1d4ed8,#38bdf8)',
        ],
        [
            'title' => 'Técnicas avançadas',
            'desc' => 'Métodos, protocolos e progressões para prescrição.',
            'icon' => 'ri-flask-line',
            'route' => Route::has('tools.import.protocols') ? 'tools.import.protocols' : null,
            'gradient' => 'linear-gradient(135deg,#7c3aed,#c084fc)',
        ],
        [
            'title' => 'Meus treinos',
            'desc' => 'Acesse prescrições e fichas de treino cadastradas.',
            'icon' => 'ri-run-line',
            'route' => 'workouts.index',
            'gradient' => 'linear-gradient(135deg,#0f766e,#2dd4bf)',
        ],
        [
            'title' => 'Treinos Predefinidos',
            'desc' => 'Modelos prontos para acelerar a montagem de novas fichas.',
            'icon' => 'ri-list-check-3',
            'route' => 'workout-templates.index',
            'gradient' => 'linear-gradient(135deg,#b45309,#f59e0b)',
        ],
        [
            'title' => 'Cursos',
            'desc' => 'Trilhas e conteúdos da biblioteca do coach.',
            'icon' => 'ri-graduation-cap-line',
            'route' => 'library.courses',
            'gradient' => 'linear-gradient(135deg,#4f46e5,#8b5cf6)',
        ],
        [
            'title' => 'Meus Planos de Cardio',
            'desc' => 'Protocolos de cardio atribuídos nos perfis dos clientes.',
            'icon' => 'ri-heart-pulse-line',
            'route' => 'members.index',
            'gradient' => 'linear-gradient(135deg,#be123c,#fb7185)',
        ],
    ];

    $helpLinks = [
        ['title' => 'Abrir suporte', 'desc' => 'Tire dúvidas sobre bibliotecas e prescrição.', 'route' => 'support-tickets.index', 'icon' => 'ri-question-answer-line'],
        ['title' => 'Nova prescrição', 'desc' => 'Comece um treino com os dados locais.', 'route' => 'workouts.create', 'icon' => 'ri-add-circle-line'],
        ['title' => 'Importar protocolos', 'desc' => 'Acompanhe a área local de importação.', 'route' => 'tools.import.protocols', 'icon' => 'ri-upload-cloud-line'],
    ];
@endphp

<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Treinamento</h1>
            <div class="prime-clients-counters">
                <span class="prime-clients-counter">
                    <i class="ri-book-open-line"></i>
                    Biblioteca local
                </span>
            </div>
        </div>
        <div class="prime-clients-toolbar__right">
            <a href="{{ route('library.hub') }}" class="prime-btn-ghost">
                <i class="ri-layout-grid-line"></i> Hub biblioteca
            </a>
            <a href="{{ route('workouts.create') }}" class="prime-btn-primary">
                <i class="ri-add-line"></i> Novo treino
            </a>
        </div>
    </div>

    <p class="prime-page-sub mb-0">Acesse exercícios, treinos, técnicas e áreas de apoio para montar prescrições no padrão Prime.</p>

    <div class="row g-3">
        @foreach($actionCards as $card)
            @php
                $hasRoute = filled($card['route']) && Route::has($card['route']);
                $href = $hasRoute ? route($card['route']) : '#';
            @endphp
            <div class="col-xl-4 col-md-6">
                <a href="{{ $href }}" class="prime-client-card text-decoration-none h-100 {{ $hasRoute ? '' : 'opacity-75' }}" @if(! $hasRoute) aria-disabled="true" onclick="return false;" @endif>
                    <div class="prime-client-card__main">
                        <div class="prime-client-card__avatar" style="background:{{ $card['gradient'] }}">
                            <i class="{{ $card['icon'] }}"></i>
                        </div>
                        <div class="prime-client-card__identity">
                            <div class="prime-client-card__name">{{ $card['title'] }}</div>
                            <div class="prime-client-card__meta">{{ $card['desc'] }}</div>
                            @if(! $hasRoute)
                                <span class="prime-chip mt-2">Em breve</span>
                            @endif
                        </div>
                    </div>
                    <div class="prime-client-card__actions">
                        <i class="ri-arrow-right-s-line prime-client-card__chevron"></i>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="prime-panel prime-panel--compact">
        <div class="prime-panel-label mb-3">Central de Ajuda</div>
        @foreach($helpLinks as $link)
            @if(Route::has($link['route']))
                <a href="{{ route($link['route']) }}" class="prime-help-row text-decoration-none">
                    <span><i class="{{ $link['icon'] }} me-2"></i>{{ $link['title'] }}</span>
                    <small class="text-muted">{{ $link['desc'] }}</small>
                </a>
            @endif
        @endforeach
    </div>
</div>
@endsection
