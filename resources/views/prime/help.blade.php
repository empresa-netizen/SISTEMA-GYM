@extends('layouts.master')

@section('title', 'Suporte')

@push('styles')
<style>
    .prime-help-hub {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .prime-help-hero {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 0.65rem;
        padding: 2.25rem 1rem 1.35rem;
    }

    .prime-help-hero__icon {
        width: 4.75rem;
        height: 4.75rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        border: 1px solid rgba(59, 130, 246, 0.35);
        background: radial-gradient(circle at 30% 20%, rgba(96, 165, 250, 0.35), rgba(59, 130, 246, 0.08) 52%, rgba(15, 23, 42, 0.92));
        color: #93c5fd;
        font-size: 2.65rem;
        font-weight: 800;
        box-shadow: 0 24px 60px rgba(37, 99, 235, 0.2);
    }

    .prime-help-hero__actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 0.35rem;
    }

    .prime-help-section {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .prime-help-section__head {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: space-between;
        gap: 0.5rem;
    }

    .prime-help-service-card {
        min-height: 11rem;
    }

    .prime-help-services-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .prime-help-service-card .prime-app-card__icon {
        width: 3rem;
        height: 3rem;
        flex-basis: 3rem;
        border-radius: 1rem;
        font-size: 1.35rem;
    }

    .prime-help-tutorials {
        display: grid;
        gap: 0.55rem;
    }

    .prime-help-tutorial {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.85rem 0.95rem;
        border: 1px solid var(--prime-border);
        border-radius: 0.9rem;
        background: rgba(15, 23, 42, 0.58);
    }

    .prime-help-tutorial__body {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        min-width: 0;
    }

    .prime-help-tutorial__icon {
        width: 2.35rem;
        height: 2.35rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 2.35rem;
        border-radius: 0.75rem;
        background: var(--prime-blue-soft);
        color: var(--prime-blue);
        font-size: 1.05rem;
    }

    .prime-help-tutorial__title {
        display: block;
        color: var(--prime-text);
        font-weight: 700;
    }

    .prime-help-tutorial__meta {
        display: block;
        color: var(--prime-muted);
        font-size: 0.78rem;
    }

    .prime-help-topic-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.75rem;
    }

    @media (max-width: 1199.98px) {
        .prime-help-topic-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 575.98px) {
        .prime-help-hero {
            padding-top: 1.35rem;
        }

        .prime-help-services-grid,
        .prime-help-topic-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
@php
    $serviceCards = [
        [
            'title' => 'Comunidade',
            'description' => 'Converse com alunos e acompanhe grupos locais da plataforma.',
            'icon' => 'ri-community-line',
            'route' => 'community.index',
            'action' => 'Abrir comunidade',
            'status' => 'Local',
        ],
        [
            'title' => 'WhatsApp Contato',
            'description' => 'Canal espelhado do Prime sem envio externo neste ambiente.',
            'icon' => 'ri-whatsapp-line',
            'route' => null,
            'action' => 'Contato indisponível',
            'status' => 'Stub',
        ],
    ];

    $tutorials = [
        ['title' => 'Como acompanhar vendas e recebíveis?', 'meta' => 'Financeiro local · 3 min', 'icon' => 'ri-wallet-3-line', 'route' => 'finance.index'],
        ['title' => 'Como prescrever treino para um aluno?', 'meta' => 'Prescrições · 5 min', 'icon' => 'ri-run-line', 'route' => 'prescriptions.index'],
        ['title' => 'Como organizar grupos da comunidade?', 'meta' => 'Comunidade · 4 min', 'icon' => 'ri-group-line', 'route' => 'community.index'],
        ['title' => 'Como criar cursos e conteúdos?', 'meta' => 'Meus Cursos · 6 min', 'icon' => 'ri-graduation-cap-line', 'route' => 'library.courses'],
    ];

    $topics = [
        ['title' => 'Financeiro', 'description' => 'Saldo, vendas, taxas e relatórios.', 'icon' => 'ri-wallet-3-line', 'route' => 'finance.index'],
        ['title' => 'Alunos', 'description' => 'Clientes, grupos, renovações e presença.', 'icon' => 'ri-user-heart-line', 'route' => 'members.index'],
        ['title' => 'Dieta', 'description' => 'Alimentos, cardápios e prescrições.', 'icon' => 'ri-restaurant-line', 'route' => 'library.diet.index'],
        ['title' => 'Treinos', 'description' => 'Biblioteca, exercícios e prescrições.', 'icon' => 'ri-dumbbell-line', 'route' => 'library.workout'],
        ['title' => 'Comunidade', 'description' => 'Grupos, posts e interação local.', 'icon' => 'ri-community-line', 'route' => 'community.index'],
        ['title' => 'Produtos', 'description' => 'Planos, cupons e recuperação.', 'icon' => 'ri-shopping-bag-3-line', 'route' => 'products.hub'],
        ['title' => 'Apps', 'description' => 'Integrações locais e status.', 'icon' => 'ri-smartphone-line', 'route' => 'apps.index'],
        ['title' => 'Configurações', 'description' => 'Conta, marca e preferências.', 'icon' => 'ri-settings-3-line', 'route' => 'settings.index'],
    ];
@endphp

<div class="prime-help-hub">
    <section class="prime-panel prime-pay-banner prime-help-hero">
        <span class="prime-help-hero__icon" aria-hidden="true">?</span>
        <div>
            <h1 class="prime-page-title mb-2">Suporte</h1>
            <p class="prime-page-sub mb-0">Encontre tutoriais, canais de atendimento e atalhos para resolver dúvidas no ambiente local.</p>
        </div>
        <div class="prime-help-hero__actions">
            <a href="{{ route('support-tickets.create') }}" class="prime-btn-primary">
                <i class="ri-add-line"></i> Abrir chamado
            </a>
            <a href="{{ route('support-tickets.index') }}" class="prime-btn-ghost">
                <i class="ri-inbox-line"></i> Meus tickets
            </a>
        </div>
    </section>

    <section class="prime-help-section">
        <div class="prime-help-section__head">
            <div>
                <span class="prime-section-pill">Atendimento</span>
                <h2 class="prime-section-title h5 mb-0 mt-2">Escolha um canal</h2>
            </div>
            <span class="prime-chip prime-chip--success">Sem APIs externas</span>
        </div>

        <div class="prime-apps-grid prime-help-services-grid">
            @foreach($serviceCards as $card)
                @php $hasRoute = $card['route'] && Route::has($card['route']); @endphp
                @if($hasRoute)
                    <a href="{{ route($card['route']) }}" class="prime-app-card prime-help-service-card @if($loop->first) prime-app-card--accent @endif">
                        <span class="prime-app-card__icon"><i class="{{ $card['icon'] }}"></i></span>
                        <span class="prime-app-card__content">
                            <span class="prime-app-card__title">{{ $card['title'] }}</span>
                            <span class="prime-app-card__description">{{ $card['description'] }}</span>
                            <span class="prime-btn-ghost prime-btn-ghost--sm mt-2 align-self-start">{{ $card['action'] }}</span>
                        </span>
                        <span class="prime-chip">{{ $card['status'] }}</span>
                    </a>
                @else
                    <div class="prime-app-card prime-help-service-card">
                        <span class="prime-app-card__icon"><i class="{{ $card['icon'] }}"></i></span>
                        <span class="prime-app-card__content">
                            <span class="prime-app-card__title">{{ $card['title'] }}</span>
                            <span class="prime-app-card__description">{{ $card['description'] }}</span>
                            <button type="button" class="prime-btn-ghost prime-btn-ghost--sm mt-2 align-self-start" disabled>{{ $card['action'] }}</button>
                        </span>
                        <span class="prime-chip">{{ $card['status'] }}</span>
                    </div>
                @endif
            @endforeach
        </div>
    </section>

    <section class="prime-help-section">
        <div class="prime-help-section__head">
            <div>
                <span class="prime-section-pill">Tutoriais</span>
                <h2 class="prime-section-title h5 mb-0 mt-2">Tutoriais em Destaque</h2>
            </div>
        </div>

        <div class="prime-panel prime-panel--compact">
            <div class="prime-help-tutorials">
                @foreach($tutorials as $tutorial)
                    @php $hasRoute = Route::has($tutorial['route']); @endphp
                    <div class="prime-help-tutorial">
                        <div class="prime-help-tutorial__body">
                            <span class="prime-help-tutorial__icon"><i class="{{ $tutorial['icon'] }}"></i></span>
                            <span>
                                <span class="prime-help-tutorial__title">{{ $tutorial['title'] }}</span>
                                <span class="prime-help-tutorial__meta">{{ $tutorial['meta'] }}</span>
                            </span>
                        </div>
                        @if($hasRoute)
                            <a href="{{ route($tutorial['route']) }}" class="prime-btn-ghost prime-btn-ghost--sm">
                                <i class="ri-play-circle-line"></i> Assistir
                            </a>
                        @else
                            <button type="button" class="prime-btn-ghost prime-btn-ghost--sm" disabled>
                                <i class="ri-play-circle-line"></i> Assistir
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="prime-help-section">
        <div class="prime-help-section__head">
            <div>
                <span class="prime-section-pill">Temas</span>
                <h2 class="prime-section-title h5 mb-0 mt-2">Escolha um tema</h2>
            </div>
            <a href="{{ route('support-tickets.index') }}" class="prime-btn-ghost prime-btn-ghost--sm">
                <i class="ri-ticket-2-line"></i> Histórico de tickets
            </a>
        </div>

        <div class="prime-help-topic-grid">
            @foreach($topics as $topic)
                @php $hasRoute = Route::has($topic['route']); @endphp
                @if($hasRoute)
                    <a href="{{ route($topic['route']) }}" class="prime-app-card">
                        <span class="prime-app-card__icon"><i class="{{ $topic['icon'] }}"></i></span>
                        <span class="prime-app-card__content">
                            <span class="prime-app-card__title">{{ $topic['title'] }}</span>
                            <span class="prime-app-card__description">{{ $topic['description'] }}</span>
                        </span>
                    </a>
                @else
                    <div class="prime-app-card">
                        <span class="prime-app-card__icon"><i class="{{ $topic['icon'] }}"></i></span>
                        <span class="prime-app-card__content">
                            <span class="prime-app-card__title">{{ $topic['title'] }}</span>
                            <span class="prime-app-card__description">{{ $topic['description'] }}</span>
                        </span>
                        <span class="prime-chip">Em breve</span>
                    </div>
                @endif
            @endforeach
        </div>
    </section>
</div>
@endsection
