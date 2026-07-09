@extends('layouts.master')

@section('title', 'Minha conta')

@section('content')
@php
    $user = auth()->user();
    $initials = collect(explode(' ', $user->name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->join('');
@endphp

<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Minha conta</h1>
            <div class="prime-clients-counters">
                <span class="prime-clients-counter">
                    <i class="ri-user-settings-line"></i>
                    Conta profissional
                </span>
            </div>
        </div>
        <div class="prime-clients-toolbar__right">
            <a href="{{ route('settings.index') }}" class="prime-btn-ghost">
                <i class="ri-settings-3-line"></i> Configurações
            </a>
            <a href="{{ route('support-tickets.create') }}" class="prime-btn-primary">
                <i class="ri-add-line"></i> Abrir chamado
            </a>
        </div>
    </div>

    <p class="prime-page-sub mb-0">Perfil, preferências e configurações da {{ config('brand.name', 'MGTEAM FITNESS & HEALTH') }}.</p>

    <div class="prime-panel prime-panel--compact">
        <div class="d-flex flex-wrap align-items-center gap-3">
            <div class="prime-logo-mark" style="width:3.5rem;height:3.5rem;font-size:1.1rem;">{{ $initials }}</div>
            <div class="flex-grow-1">
                <h2 class="h5 mb-1">{{ $user->name }}</h2>
                <p class="text-muted small mb-0">{{ $user->email }}</p>
            </div>
            <div class="text-md-end">
                <span class="prime-chip prime-chip--info">Conta profissional</span>
                <p class="small text-muted mb-0 mt-2">Membro desde {{ $user->created_at?->locale('pt_BR')->translatedFormat('F \d\e Y') ?? '—' }}</p>
            </div>
        </div>
    </div>

    <p class="prime-section-label">Conta</p>
    <h2 class="prime-section-title">Configurações</h2>

    <div class="row g-2 mb-2">
        @foreach([
            ['title' => 'Perfil e configurações', 'route' => 'settings.index', 'icon' => 'ri-user-settings-line', 'desc' => 'Dados da conta, marca e preferências do coach.'],
            ['title' => 'Financeiro', 'route' => 'finance.index', 'icon' => 'ri-wallet-3-line', 'desc' => 'Saldo, faturamento, faturas e transações.'],
            ['title' => 'Suporte', 'route' => 'support-tickets.index', 'icon' => 'ri-customer-service-2-line', 'desc' => 'Chamados e ajuda da plataforma.'],
        ] as $card)
        <div class="col-md-6 col-xl-4">
            <a href="{{ route($card['route']) }}" class="text-decoration-none">
                <div class="prime-panel prime-panel--compact prime-panel--hover h-100">
                    <i class="{{ $card['icon'] }} fs-3 text-primary mb-2 d-block"></i>
                    <h3 class="h6 mb-1">{{ $card['title'] }}</h3>
                    <p class="text-muted small mb-0">{{ $card['desc'] }}</p>
                </div>
            </a>
        </div>
        @endforeach
    </div>

    <p class="prime-section-label">Plataforma</p>
    <h2 class="prime-section-title">Ferramentas</h2>

    <div class="row g-2 mb-2">
        @foreach([
            ['title' => 'Apps Mobile', 'route' => 'apps.index', 'icon' => 'ri-smartphone-line', 'desc' => config('brand.short', 'MGTEAM').' Pro, Aluno e API mobile.'],
            ['title' => 'Notificações', 'route' => 'notifications.index', 'icon' => 'ri-notification-3-line', 'desc' => 'Alertas e lembretes do sistema.'],
            ['title' => 'Produtos', 'route' => 'products.hub', 'icon' => 'ri-price-tag-3-line', 'desc' => 'Planos, cupons e monetização.'],
        ] as $card)
        <div class="col-md-6 col-xl-4">
            <a href="{{ route($card['route']) }}" class="text-decoration-none">
                <div class="prime-panel prime-panel--compact prime-panel--hover h-100">
                    <i class="{{ $card['icon'] }} fs-3 text-primary mb-2 d-block"></i>
                    <h3 class="h6 mb-1">{{ $card['title'] }}</h3>
                    <p class="text-muted small mb-0">{{ $card['desc'] }}</p>
                </div>
            </a>
        </div>
        @endforeach
    </div>

    <div class="prime-panel prime-panel--compact">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <div class="prime-panel-label mb-1">PRECISA DE AJUDA?</div>
                <p class="mb-0 small text-muted">Nossa equipe responde em até 24h úteis em {{ config('brand.support_email', 'suporte@mgteam.app') }}.</p>
            </div>
            <a href="{{ route('support-tickets.create') }}" class="prime-btn-ghost prime-btn-ghost--sm">
                <i class="ri-add-line"></i> Abrir chamado
            </a>
        </div>
    </div>
</div>
@endsection
