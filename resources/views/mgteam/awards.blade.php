@extends('layouts.master')

@section('title', 'Premiações')

@section('content')
@php
    $rewards = [
        [
            'title' => 'Mousepad MGTEAM',
            'milestone' => 'R$ 10K',
            'description' => 'Premiação inicial para celebrar os R$ 10 mil iniciais faturados com o ecossistema MGTEAM.',
            'icon' => 'ri-mouse-line',
            'active' => $revenueTotal >= 10000,
        ],
        [
            'title' => 'Placa MGTEAM 100K',
            'milestone' => 'R$ 100K',
            'description' => 'Reconhecimento para coaches que consolidam operação, recorrência e crescimento sustentável.',
            'icon' => 'ri-award-line',
            'active' => $revenueTotal >= 100000,
        ],
        [
            'title' => 'Placa MGTEAM 1M',
            'milestone' => 'R$ 1M',
            'description' => 'Marco máximo da jornada MGTEAM para negócios que alcançam escala de alto impacto.',
            'icon' => 'ri-vip-crown-line',
            'active' => $revenueTotal >= 1000000,
        ],
    ];
@endphp

<div class="mg-clients-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Premiações</h1>
            <p class="mg-page-sub mb-0">Reconhecimento pelo seu esforço e dedicação</p>
        </div>
        <div class="mg-clients-toolbar__right">
            <a href="{{ route('dashboard') }}" class="mg-btn-ghost">Resumo</a>
            <a href="{{ route('finance.index') }}" class="mg-btn-primary">Financeiro</a>
        </div>
    </div>

    <div class="row g-3 align-items-stretch">
        <div class="col-lg-7">
            <section class="mg-panel mg-awards-info">
                <div class="mg-awards-kicker"><i class="ri-sparkling-2-line"></i> Programa MGTEAM Premiações</div>
                <h2>Seu crescimento vira reconhecimento físico.</h2>
                <p>
                    Acompanhe os marcos de faturamento registrados localmente no financeiro e mantenha seus dados completos
                    para facilitar a validação e o envio das premiações MGTEAM.
                </p>

                <div class="mg-awards-steps">
                    <div>
                        <span>01</span>
                        <strong>Como qualificar</strong>
                        <p>Registre vendas e recebimentos no financeiro local até atingir os marcos do programa.</p>
                    </div>
                    <div>
                        <span>02</span>
                        <strong>Como receber</strong>
                        <p>Com o marco atingido, confira seus dados de contato e endereço para envio da premiação.</p>
                    </div>
                </div>
            </section>
        </div>

        <div class="col-lg-5">
            <section class="mg-panel mg-awards-checklist">
                <div class="mg-panel-label">Checklist de envio</div>
                <div class="mg-awards-checklist__grid">
                    @foreach($profileChecklist as $item)
                        <div class="mg-awards-check {{ $item['complete'] ? 'is-complete' : 'is-missing' }}">
                            <i class="{{ $item['complete'] ? 'ri-checkbox-circle-fill' : 'ri-circle-line' }}"></i>
                            <span>{{ $item['label'] }}</span>
                        </div>
                    @endforeach
                </div>
                <p class="mg-panel-hint mb-0">Os checks usam apenas campos existentes no usuário autenticado local.</p>
            </section>
        </div>
    </div>

    <section class="mg-panel mg-awards-progress">
        <div class="mg-awards-progress__main">
            <div>
                <div class="mg-panel-label">Marco atual</div>
                <h2>R$ 10K em faturamento</h2>
                <p class="mg-panel-hint mb-0">Progresso calculado com totais locais de pagamentos vinculados a invoices.</p>
            </div>
            <div class="mg-awards-progress__value">
                <span>{{ $progress }}%</span>
                <small>concluído</small>
            </div>
        </div>
        <div class="mg-goal-track mg-awards-progress__track">
            <div class="mg-goal-fill" style="width: {{ $progress }}%"></div>
        </div>
        <div class="mg-awards-progress__stats">
            <span>Faturado: <strong>R$ {{ number_format($revenueTotal, 0, ',', '.') }}</strong></span>
            <span>Meta: <strong>R$ {{ number_format($goal, 0, ',', '.') }}</strong></span>
            <span>Restante: <strong>R$ {{ number_format($remaining, 0, ',', '.') }}</strong></span>
        </div>
    </section>

    <div class="mg-awards-rewards">
        @foreach($rewards as $reward)
            <article class="mg-panel mg-award-card {{ $reward['active'] ? 'is-active' : '' }}">
                <div class="mg-award-card__icon"><i class="{{ $reward['icon'] }}"></i></div>
                <div class="mg-award-card__meta">
                    <span>{{ $reward['milestone'] }}</span>
                    <strong>{{ $reward['title'] }}</strong>
                </div>
                <p>{{ $reward['description'] }}</p>
                <div class="mg-award-card__status">
                    <i class="{{ $reward['active'] ? 'ri-checkbox-circle-fill' : 'ri-lock-line' }}"></i>
                    {{ $reward['active'] ? 'Liberado' : 'Em progresso' }}
                </div>
            </article>
        @endforeach
    </div>
</div>
@endsection
