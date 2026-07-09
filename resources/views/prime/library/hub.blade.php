@extends('layouts.master')

@section('title', 'Biblioteca')

@section('content')
<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Bibliotecas</h1>
            <p class="prime-page-sub mb-0">Templates de treino, dieta e cursos para acelerar a prescrição.</p>
        </div>
    </div>

    <div class="row g-3">
        @foreach([
            ['title' => 'Exercícios', 'desc' => 'Catálogo de movimentos e vídeos', 'route' => 'exercises.index', 'icon' => 'ri-play-circle-line', 'gradient' => 'linear-gradient(135deg,#1d4ed8,#38bdf8)'],
            ['title' => 'Treinos', 'desc' => 'Hub de treinamento e templates', 'route' => 'library.workout', 'icon' => 'ri-run-line', 'gradient' => 'linear-gradient(135deg,#0f766e,#2dd4bf)'],
            ['title' => 'Treinos predefinidos', 'desc' => 'Modelos prontos para importar', 'route' => 'workout-templates.index', 'icon' => 'ri-list-check-3', 'gradient' => 'linear-gradient(135deg,#b45309,#f59e0b)'],
            ['title' => 'Dieta', 'desc' => 'Alimentos, cardápios e fórmulas', 'route' => 'library.diet.index', 'icon' => 'ri-restaurant-line', 'gradient' => 'linear-gradient(135deg,#be123c,#fb7185)'],
            ['title' => 'Cursos', 'desc' => 'Conteúdos e trilhas do coach', 'route' => 'library.courses', 'icon' => 'ri-graduation-cap-line', 'gradient' => 'linear-gradient(135deg,#4f46e5,#8b5cf6)'],
            ['title' => 'Prescrições', 'desc' => 'Agenda de envios ao cliente', 'route' => 'prescriptions.index', 'icon' => 'ri-calendar-check-line', 'gradient' => 'linear-gradient(135deg,#334155,#94a3b8)'],
        ] as $card)
            <div class="col-xl-4 col-md-6">
                <a href="{{ route($card['route']) }}" class="prime-client-card text-decoration-none h-100">
                    <div class="prime-client-card__main">
                        <div class="prime-client-card__avatar" style="background:{{ $card['gradient'] }}">
                            <i class="{{ $card['icon'] }}"></i>
                        </div>
                        <div class="prime-client-card__identity">
                            <div class="prime-client-card__name">{{ $card['title'] }}</div>
                            <div class="prime-client-card__meta">{{ $card['desc'] }}</div>
                        </div>
                    </div>
                    <div class="prime-client-card__actions">
                        <i class="ri-arrow-right-s-line prime-client-card__chevron"></i>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</div>
@endsection
