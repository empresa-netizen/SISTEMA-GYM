@extends('layouts.master')

@section('title', 'Recuperação de carrinho')

@section('content')
@php
    $statusLabels = [
        'abandonado' => ['label' => 'Abandonado', 'chip' => 'mg-chip--danger'],
        'email_enviado' => ['label' => 'E-mail enviado', 'chip' => 'mg-chip--warn'],
        'recuperado' => ['label' => 'Recuperado', 'chip' => 'mg-chip--success'],
    ];
@endphp

<div class="mg-clients-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Recuperação de carrinho</h1>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter mg-clients-counter--pending">
                    <i class="ri-shopping-cart-2-line"></i>
                    {{ $stats['abandoned_total'] }} abandonados
                </span>
                <span class="mg-clients-counter mg-clients-counter--delivered">
                    <i class="ri-check-double-line"></i>
                    {{ $stats['recovered_count'] }} recuperados
                </span>
            </div>
        </div>
        <div class="mg-clients-toolbar__right">
            <a href="{{ route('products.hub') }}" class="mg-btn-ghost">
                <i class="ri-arrow-left-line"></i> Hub produtos
            </a>
            <button type="button" class="mg-btn-ghost" disabled title="Em breve">
                <i class="ri-download-2-line"></i> Exportar
            </button>
            <button type="button" class="mg-btn-primary" disabled title="Em breve">
                <i class="ri-mail-send-line"></i> Disparar lembrete
            </button>
        </div>
    </div>

    <div class="mg-stats-row">
        <div class="mg-stat-mini">
            <div class="mg-stat-label">Carrinhos abandonados</div>
            <div class="mg-stat-value">{{ $stats['abandoned_total'] }}</div>
            <p class="mg-panel-hint mb-0">R$ {{ number_format($stats['abandoned_value'], 2, ',', '.') }}</p>
        </div>
        <div class="mg-stat-mini">
            <div class="mg-stat-label">Recuperados</div>
            <div class="mg-stat-value text-success">{{ $stats['recovered_count'] }}</div>
            <p class="mg-panel-hint mb-0">R$ {{ number_format($stats['recovered_value'], 2, ',', '.') }}</p>
        </div>
        <div class="mg-stat-mini">
            <div class="mg-stat-label">Taxa de recuperação</div>
            <div class="mg-stat-value">{{ number_format($stats['recovery_rate'], 1, ',', '.') }}%</div>
        </div>
        <div class="mg-stat-mini">
            <div class="mg-stat-label">E-mails disparados</div>
            <div class="mg-stat-value">{{ $stats['emails_sent'] }}</div>
            <p class="mg-panel-hint mb-0">Sequência em 3 etapas</p>
        </div>
    </div>

    <div class="mg-panel mg-panel--compact">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <div class="mg-panel-label mb-1">Automação</div>
                <p class="mb-0 small text-muted">E-mails 1h, 24h e 72h após o abandono via {{ config('brand.pay', 'MGTEAM Pay') }}.</p>
            </div>
            <span class="mg-chip mg-chip--info">Ativa</span>
        </div>
    </div>

    <div class="mg-client-list">
        @forelse($carts as $cart)
            @php $status = $statusLabels[$cart['status']] ?? $statusLabels['abandonado']; @endphp
            <div class="mg-client-card mg-product-card">
                <div class="mg-client-card__main">
                    <div class="mg-client-card__avatar" style="background:linear-gradient(135deg,#b45309,#f59e0b)">
                        <i class="ri-shopping-cart-2-line"></i>
                    </div>
                    <div class="mg-client-card__identity">
                        <div class="mg-client-card__name">{{ $cart['client'] }}</div>
                        <div class="mg-client-card__meta">
                            <span>{{ $cart['email'] }}</span>
                            <span class="mg-client-card__sep">|</span>
                            <span>{{ $cart['product'] }}</span>
                            <span class="mg-client-card__sep">|</span>
                            <span>R$ {{ number_format($cart['value'], 2, ',', '.') }}</span>
                        </div>
                        <div class="mg-client-chips">
                            <span class="mg-chip {{ $status['chip'] }}">{{ $status['label'] }}</span>
                            <span class="mg-chip">{{ $cart['attempts'] }} tentativas</span>
                            <span class="mg-chip">{{ $cart['abandoned_at'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="mg-client-card__actions">
                    @if($cart['status'] !== 'recuperado')
                        <button type="button" class="mg-btn-ghost mg-btn-ghost--sm" disabled>Recuperar</button>
                    @endif
                </div>
            </div>
        @empty
            <div class="mg-empty-state">
                <i class="ri-shopping-cart-2-line"></i>
                <p>Nenhum carrinho abandonado nos últimos 30 dias.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
