@extends('layouts.master')

@section('title', 'Dieta e protocolo')

@section('content')
@php
    $actionCards = [
        [
            'title' => 'Meus alimentos',
            'desc' => 'Catálogo nutricional local para montar prescrições.',
            'icon' => 'ri-apple-line',
            'route' => 'library.diet.foods',
            'gradient' => 'linear-gradient(135deg,#15803d,#22c55e)',
        ],
        [
            'title' => 'Minhas fórmulas',
            'desc' => 'Fórmulas nutricionais e cálculos de apoio.',
            'icon' => 'ri-flask-line',
            'route' => 'library.diet.formulas',
            'gradient' => 'linear-gradient(135deg,#7c3aed,#c084fc)',
        ],
        [
            'title' => 'Meus cardápios',
            'desc' => 'Planos alimentares cadastrados na biblioteca.',
            'icon' => 'ri-restaurant-line',
            'route' => 'library.diet.menus',
            'gradient' => 'linear-gradient(135deg,#b45309,#f59e0b)',
        ],
        [
            'title' => 'Refeições predefinidas',
            'desc' => 'Modelos prontos para acelerar novas dietas.',
            'icon' => 'ri-bowl-line',
            'route' => 'library.diet.predefined-meals',
            'gradient' => 'linear-gradient(135deg,#be123c,#fb7185)',
        ],
    ];

    $helpLinks = [
        ['title' => 'Como criar seus alimentos?', 'desc' => 'Abra suporte para estruturar sua biblioteca alimentar.', 'route' => 'support-tickets.index', 'icon' => 'ri-question-answer-line'],
        ['title' => 'Como montar cardápios?', 'desc' => 'Use os cardápios locais como base de prescrição.', 'route' => 'library.diet.menus', 'icon' => 'ri-restaurant-line'],
        ['title' => 'Como prescrever dieta ao aluno?', 'desc' => 'Acesse a área local de prescrições.', 'route' => 'prescriptions.index', 'icon' => 'ri-file-list-3-line'],
    ];
@endphp

<div class="mg-clients-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Dieta e protocolo</h1>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter">
                    <i class="ri-restaurant-line"></i>
                    Alimentos, cardápios e refeições
                </span>
            </div>
        </div>
        <div class="mg-clients-toolbar__right">
            <a href="{{ route('library.hub') }}" class="mg-btn-ghost">
                <i class="ri-book-open-line"></i> Hub biblioteca
            </a>
        </div>
    </div>

    <p class="mg-page-sub mb-0">Acesse alimentos, fórmulas, cardápios e modelos de refeições no padrão MGTEAM.</p>

    <div class="row g-3">
        @foreach($actionCards as $card)
            @php
                $hasRoute = filled($card['route']) && Route::has($card['route']);
                $href = $hasRoute ? route($card['route']) : '#';
            @endphp
            <div class="col-xl-3 col-md-6">
                <a href="{{ $href }}" class="mg-client-card text-decoration-none h-100 {{ $hasRoute ? '' : 'opacity-75' }}" @if(! $hasRoute) aria-disabled="true" onclick="return false;" @endif>
                    <div class="mg-client-card__main">
                        <div class="mg-client-card__avatar" style="background:{{ $card['gradient'] }}">
                            <i class="{{ $card['icon'] }}"></i>
                        </div>
                        <div class="mg-client-card__identity">
                            <div class="mg-client-card__name">{{ $card['title'] }}</div>
                            <div class="mg-client-card__meta">{{ $card['desc'] }}</div>
                            @if(! $hasRoute)
                                <span class="mg-chip mt-2">Em breve</span>
                            @endif
                        </div>
                    </div>
                    <div class="mg-client-card__actions">
                        <i class="ri-arrow-right-s-line mg-client-card__chevron"></i>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="mg-panel mg-panel--compact">
        <div class="mg-panel-label mb-3">Central de Ajuda</div>
        @foreach($helpLinks as $link)
            @if(Route::has($link['route']))
                <a href="{{ route($link['route']) }}" class="mg-help-row text-decoration-none">
                    <span><i class="{{ $link['icon'] }} me-2"></i>{{ $link['title'] }}</span>
                    <small class="text-muted">{{ $link['desc'] }}</small>
                </a>
            @endif
        @endforeach
    </div>
</div>
@endsection
