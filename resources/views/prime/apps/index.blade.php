@extends('layouts.master')

@section('title', 'Apps')

@section('content')
<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Apps</h1>
            <div class="prime-clients-counters">
                <span class="prime-clients-counter prime-clients-counter--delivered">
                    <i class="ri-plug-2-fill"></i>
                    {{ count($integrations) }} integrações locais
                </span>
            </div>
        </div>
        <div class="prime-clients-toolbar__right">
            <span class="prime-logo-mark" style="width:2.25rem;height:2.25rem;font-size:0.9rem;">{{ config('brand.logo_mark', 'M') }}</span>
        </div>
    </div>

    <p class="prime-page-sub mb-0">Integrações espelhadas no visual Prime, apontando apenas para recursos do Laravel local.</p>

    <div class="prime-panel prime-panel--compact prime-pay-banner">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <div class="prime-panel-label mb-1">Ambiente local</div>
                <p class="mb-0 small text-muted">Nenhum card chama APIs externas; os links levam a rotas, relatórios e configurações locais.</p>
            </div>
            <span class="prime-chip prime-chip--success">Laravel + MySQL</span>
        </div>
    </div>

    <div class="prime-apps-grid">
        @foreach($integrations as $integration)
            <a href="{{ $integration['href'] }}" class="prime-app-card @if($loop->first) prime-app-card--accent @endif">
                <span class="prime-app-card__icon"><i class="{{ $integration['icon'] }}"></i></span>
                <span class="prime-app-card__content">
                    <span class="prime-app-card__title">{{ $integration['label'] }}</span>
                    <span class="prime-app-card__description">{{ $integration['description'] }}</span>
                </span>
                <span class="prime-chip @if($integration['status'] === 'Ativa') prime-chip--success @endif">{{ $integration['status'] }}</span>
            </a>
        @endforeach
    </div>
</div>
@endsection
