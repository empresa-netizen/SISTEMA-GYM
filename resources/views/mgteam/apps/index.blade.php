@extends('layouts.master')

@section('title', 'Apps')

@section('content')
<div class="mg-clients-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Apps</h1>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter mg-clients-counter--delivered">
                    <i class="ri-plug-2-fill"></i>
                    {{ count($integrations) }} integrações locais
                </span>
            </div>
        </div>
        <div class="mg-clients-toolbar__right">
            <span class="mg-logo-mark" style="width:2.25rem;height:2.25rem;font-size:0.9rem;">{{ config('brand.logo_mark', 'M') }}</span>
        </div>
    </div>

    <p class="mg-page-sub mb-0">Integrações espelhadas no visual MGTEAM, apontando apenas para recursos do Laravel local.</p>

    <div class="mg-panel mg-panel--compact mg-pay-banner">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <div class="mg-panel-label mb-1">Ambiente local</div>
                <p class="mb-0 small text-muted">Nenhum card chama APIs externas; os links levam a rotas, relatórios e configurações locais.</p>
            </div>
            <span class="mg-chip mg-chip--success">Laravel + MySQL</span>
        </div>
    </div>

    <div class="mg-apps-grid">
        @foreach($integrations as $integration)
            <a href="{{ $integration['href'] }}" class="mg-app-card @if($loop->first) mg-app-card--accent @endif">
                <span class="mg-app-card__icon"><i class="{{ $integration['icon'] }}"></i></span>
                <span class="mg-app-card__content">
                    <span class="mg-app-card__title">{{ $integration['label'] }}</span>
                    <span class="mg-app-card__description">{{ $integration['description'] }}</span>
                </span>
                <span class="mg-chip @if($integration['status'] === 'Ativa') mg-chip--success @endif">{{ $integration['status'] }}</span>
            </a>
        @endforeach
    </div>
</div>
@endsection
