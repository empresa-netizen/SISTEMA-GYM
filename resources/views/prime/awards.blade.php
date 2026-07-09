@extends('layouts.master')

@section('title', 'Premiações')

@section('content')
@php
    $rewards = [
        [
            'title' => 'Mousepad MGTEAM',
            'milestone' => 'R$ 10K',
            'description' => 'Primeira premiação para celebrar os primeiros R$ 10 mil faturados com o ecossistema MGTEAM.',
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
            'description' => 'Marco máximo da jornada Prime para negócios que alcançam escala de alto impacto.',
            'icon' => 'ri-vip-crown-line',
            'active' => $revenueTotal >= 1000000,
        ],
    ];
@endphp

<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Premiações</h1>
            <p class="prime-page-sub mb-0">Reconhecimento pelo seu esforço e dedicação</p>
        </div>
        <div class="prime-clients-toolbar__right">
            <a href="{{ route('dashboard') }}" class="prime-btn-ghost">Resumo</a>
            <a href="{{ route('finance.index') }}" class="prime-btn-primary">Financeiro</a>
        </div>
    </div>

    <div class="row g-3 align-items-stretch">
        <div class="col-lg-7">
            <section class="prime-panel prime-awards-info">
                <div class="prime-awards-kicker"><i class="ri-sparkling-2-line"></i> Programa Prime Premiações</div>
                <h2>Seu crescimento vira reconhecimento físico.</h2>
                <p>
                    Acompanhe os marcos de faturamento registrados localmente no financeiro e mantenha seus dados completos
                    para facilitar a validação e o envio das premiações MGTEAM.
                </p>

                <div class="prime-awards-steps">
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
            <section class="prime-panel prime-awards-checklist">
                <div class="prime-panel-label">Checklist de envio</div>
                <div class="prime-awards-checklist__grid">
                    @foreach($profileChecklist as $item)
                        <div class="prime-awards-check {{ $item['complete'] ? 'is-complete' : 'is-missing' }}">
                            <i class="{{ $item['complete'] ? 'ri-checkbox-circle-fill' : 'ri-circle-line' }}"></i>
                            <span>{{ $item['label'] }}</span>
                        </div>
                    @endforeach
                </div>
                <p class="prime-panel-hint mb-0">Os checks usam apenas campos existentes no usuário autenticado local.</p>
            </section>
        </div>
    </div>

    <section class="prime-panel prime-awards-progress">
        <div class="prime-awards-progress__main">
            <div>
                <div class="prime-panel-label">Marco atual</div>
                <h2>R$ 10K em faturamento</h2>
                <p class="prime-panel-hint mb-0">Progresso calculado com totais locais de pagamentos vinculados a invoices.</p>
            </div>
            <div class="prime-awards-progress__value">
                <span>{{ $progress }}%</span>
                <small>concluído</small>
            </div>
        </div>
        <div class="prime-goal-track prime-awards-progress__track">
            <div class="prime-goal-fill" style="width: {{ $progress }}%"></div>
        </div>
        <div class="prime-awards-progress__stats">
            <span>Faturado: <strong>R$ {{ number_format($revenueTotal, 0, ',', '.') }}</strong></span>
            <span>Meta: <strong>R$ {{ number_format($goal, 0, ',', '.') }}</strong></span>
            <span>Restante: <strong>R$ {{ number_format($remaining, 0, ',', '.') }}</strong></span>
        </div>
    </section>

    <div class="prime-awards-rewards">
        @foreach($rewards as $reward)
            <article class="prime-panel prime-award-card {{ $reward['active'] ? 'is-active' : '' }}">
                <div class="prime-award-card__icon"><i class="{{ $reward['icon'] }}"></i></div>
                <div class="prime-award-card__meta">
                    <span>{{ $reward['milestone'] }}</span>
                    <strong>{{ $reward['title'] }}</strong>
                </div>
                <p>{{ $reward['description'] }}</p>
                <div class="prime-award-card__status">
                    <i class="{{ $reward['active'] ? 'ri-checkbox-circle-fill' : 'ri-lock-line' }}"></i>
                    {{ $reward['active'] ? 'Liberado' : 'Em progresso' }}
                </div>
            </article>
        @endforeach
    </div>
</div>
@endsection
