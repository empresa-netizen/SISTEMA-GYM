@extends('layouts.master')

@section('title', 'Relatórios')

@section('content')
<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Financeiro</h1>
            <div class="prime-clients-counters">
                <span class="prime-clients-counter prime-clients-counter--delivered">
                    <i class="ri-line-chart-fill"></i>
                    R$ {{ number_format($monthRevenue, 2, ',', '.') }} este mês
                </span>
            </div>
        </div>
        <div class="prime-clients-toolbar__right">
            <span class="prime-chip prime-chip--info">{{ config('brand.pay', 'MGTEAM Pay') }}</span>
        </div>
    </div>

    <p class="prime-page-sub mb-0">Relatórios e tendências do {{ config('brand.pay', 'MGTEAM Pay') }}.</p>

    @include('prime.finance._tabs', ['active' => 'reports'])

    <div class="row g-2">
        <div class="col-md-4">
            <div class="prime-stat-card prime-stat-card--compact">
                <div class="p-2">
                    <div class="prime-stat-label">Receita total</div>
                    <div class="prime-stat-value">R$ {{ number_format($availableBalance, 2, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="prime-stat-card prime-stat-card--compact">
                <div class="p-2">
                    <div class="prime-stat-label">Este mês</div>
                    <div class="prime-stat-value text-success">R$ {{ number_format($monthRevenue, 2, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="prime-stat-card prime-stat-card--compact">
                <div class="p-2">
                    <div class="prime-stat-label">Pendente</div>
                    <div class="prime-stat-value text-warning">R$ {{ number_format($pendingBalance, 2, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-2">
        <div class="col-lg-8">
            <div class="prime-panel prime-panel--compact h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="prime-panel-label mb-0">RECEITA POR MÊS</div>
                    <span class="prime-chip">Histórico</span>
                </div>
                @if($byMonth->isEmpty())
                    <div class="prime-empty-state prime-empty-state--compact">
                        <i class="ri-bar-chart-box-line"></i>
                        <p>Sem dados suficientes para o relatório.</p>
                    </div>
                @else
                    @php $max = max($byMonth->max('total'), 1); @endphp
                    <div class="prime-report-bars">
                        @foreach($byMonth as $row)
                            @php
                                $label = \Carbon\Carbon::createFromFormat('Y-m', $row->month)->translatedFormat('M Y');
                                $pct = ($row->total / $max) * 100;
                            @endphp
                            <div class="prime-report-row">
                                <div class="prime-report-label">{{ $label }}</div>
                                <div class="prime-report-track"><div class="prime-report-fill" style="width: {{ $pct }}%"></div></div>
                                <div class="prime-report-value">R$ {{ number_format($row->total, 0, ',', '.') }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        <div class="col-lg-4">
            <div class="prime-panel prime-panel--compact h-100">
                <h2 class="prime-section-title h6 mb-3">Resumo analítico</h2>
                <div class="prime-list-row">
                    <div class="prime-list-body">
                        <div class="prime-list-title">Receita líquida estimada</div>
                        <div class="prime-list-sub">Total - pendências</div>
                    </div>
                    <strong>R$ {{ number_format(max($availableBalance - $pendingBalance, 0), 2, ',', '.') }}</strong>
                </div>
                <div class="prime-list-row">
                    <div class="prime-list-body">
                        <div class="prime-list-title">Representatividade do mês</div>
                        <div class="prime-list-sub">Mês atual / receita total</div>
                    </div>
                    <strong>{{ $availableBalance > 0 ? number_format(($monthRevenue / $availableBalance) * 100, 1, ',', '.') : '0,0' }}%</strong>
                </div>
                <div class="prime-list-row">
                    <div class="prime-list-body">
                        <div class="prime-list-title">Risco financeiro</div>
                        <div class="prime-list-sub">Pendências sobre o total</div>
                    </div>
                    @if($availableBalance > 0 && (($pendingBalance / $availableBalance) > 0.2))
                        <span class="prime-chip prime-chip--danger">Alto</span>
                    @else
                        <span class="prime-chip prime-chip--success">Controlado</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
