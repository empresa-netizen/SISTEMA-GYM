@extends('layouts.master')

@section('title', 'Recuperação de carrinho')

@section('content')
@php
    $statusLabels = [
        'abandonado' => ['label' => 'Abandonado', 'chip' => 'prime-chip--danger'],
        'email_enviado' => ['label' => 'E-mail enviado', 'chip' => 'prime-chip--warn'],
        'recuperado' => ['label' => 'Recuperado', 'chip' => 'prime-chip--success'],
    ];
@endphp

<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Recuperação de carrinho</h1>
            <div class="prime-clients-counters">
                <span class="prime-clients-counter prime-clients-counter--pending">
                    <i class="ri-shopping-cart-2-line"></i>
                    {{ $stats['abandoned_total'] }} abandonados
                </span>
                <span class="prime-clients-counter prime-clients-counter--delivered">
                    <i class="ri-check-double-line"></i>
                    {{ $stats['recovered_count'] }} recuperados
                </span>
            </div>
        </div>
        <div class="prime-clients-toolbar__right">
            <a href="{{ route('products.hub') }}" class="prime-btn-ghost">
                <i class="ri-arrow-left-line"></i> Hub produtos
            </a>
            <button type="button" class="prime-btn-ghost" disabled title="Em breve">
                <i class="ri-download-2-line"></i> Exportar
            </button>
            <button type="button" class="prime-btn-primary" disabled title="Em breve">
                <i class="ri-mail-send-line"></i> Disparar lembrete
            </button>
        </div>
    </div>

    <div class="prime-stats-row">
        <div class="prime-stat-mini">
            <div class="prime-stat-label">Carrinhos abandonados</div>
            <div class="prime-stat-value">{{ $stats['abandoned_total'] }}</div>
            <p class="prime-panel-hint mb-0">R$ {{ number_format($stats['abandoned_value'], 2, ',', '.') }}</p>
        </div>
        <div class="prime-stat-mini">
            <div class="prime-stat-label">Recuperados</div>
            <div class="prime-stat-value text-success">{{ $stats['recovered_count'] }}</div>
            <p class="prime-panel-hint mb-0">R$ {{ number_format($stats['recovered_value'], 2, ',', '.') }}</p>
        </div>
        <div class="prime-stat-mini">
            <div class="prime-stat-label">Taxa de recuperação</div>
            <div class="prime-stat-value">{{ number_format($stats['recovery_rate'], 1, ',', '.') }}%</div>
        </div>
        <div class="prime-stat-mini">
            <div class="prime-stat-label">E-mails disparados</div>
            <div class="prime-stat-value">{{ $stats['emails_sent'] }}</div>
            <p class="prime-panel-hint mb-0">Sequência em 3 etapas</p>
        </div>
    </div>

    <div class="prime-panel prime-panel--compact">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <div class="prime-panel-label mb-1">Automação</div>
                <p class="mb-0 small text-muted">E-mails 1h, 24h e 72h após o abandono via {{ config('brand.pay', 'MGTEAM Pay') }}.</p>
            </div>
            <span class="prime-chip prime-chip--info">Ativa</span>
        </div>
    </div>

    <div class="prime-client-list">
        @forelse($carts as $cart)
            @php $status = $statusLabels[$cart['status']] ?? $statusLabels['abandonado']; @endphp
            <div class="prime-client-card prime-product-card">
                <div class="prime-client-card__main">
                    <div class="prime-client-card__avatar" style="background:linear-gradient(135deg,#b45309,#f59e0b)">
                        <i class="ri-shopping-cart-2-line"></i>
                    </div>
                    <div class="prime-client-card__identity">
                        <div class="prime-client-card__name">{{ $cart['client'] }}</div>
                        <div class="prime-client-card__meta">
                            <span>{{ $cart['email'] }}</span>
                            <span class="prime-client-card__sep">|</span>
                            <span>{{ $cart['product'] }}</span>
                            <span class="prime-client-card__sep">|</span>
                            <span>R$ {{ number_format($cart['value'], 2, ',', '.') }}</span>
                        </div>
                        <div class="prime-client-chips">
                            <span class="prime-chip {{ $status['chip'] }}">{{ $status['label'] }}</span>
                            <span class="prime-chip">{{ $cart['attempts'] }} tentativas</span>
                            <span class="prime-chip">{{ $cart['abandoned_at'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="prime-client-card__actions">
                    @if($cart['status'] !== 'recuperado')
                        <button type="button" class="prime-btn-ghost prime-btn-ghost--sm" disabled>Recuperar</button>
                    @endif
                </div>
            </div>
        @empty
            <div class="prime-empty-state">
                <i class="ri-shopping-cart-2-line"></i>
                <p>Nenhum carrinho abandonado nos últimos 30 dias.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
