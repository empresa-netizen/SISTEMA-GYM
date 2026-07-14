@extends('layouts.master')

@section('title', 'Relatórios')

@section('content')
<div class="mg-clients-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Financeiro</h1>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter mg-clients-counter--delivered">
                    <i class="ri-line-chart-fill"></i>
                    R$ {{ number_format($monthRevenue, 2, ',', '.') }} este mês
                </span>
            </div>
        </div>
        <div class="mg-clients-toolbar__right">
            <span class="mg-chip mg-chip--info">{{ config('brand.pay', 'MGTEAM Pay') }}</span>
        </div>
    </div>

    <p class="mg-page-sub mb-0">Relatórios e tendências do {{ config('brand.pay', 'MGTEAM Pay') }}.</p>

    @include('mgteam.finance._tabs', ['active' => 'reports'])

    <div class="row g-2">
        <div class="col-md-4">
            <div class="mg-stat-card mg-stat-card--compact">
                <div class="p-2">
                    <div class="mg-stat-label">Receita total</div>
                    <div class="mg-stat-value">R$ {{ number_format($availableBalance, 2, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="mg-stat-card mg-stat-card--compact">
                <div class="p-2">
                    <div class="mg-stat-label">Este mês</div>
                    <div class="mg-stat-value text-success">R$ {{ number_format($monthRevenue, 2, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="mg-stat-card mg-stat-card--compact">
                <div class="p-2">
                    <div class="mg-stat-label">Pendente</div>
                    <div class="mg-stat-value text-warning">R$ {{ number_format($pendingBalance, 2, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-2">
        <div class="col-lg-8">
            <div class="mg-panel mg-panel--compact h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="mg-panel-label mb-0">RECEITA POR MÊS</div>
                    <span class="mg-chip">Histórico</span>
                </div>
                @if($byMonth->isEmpty())
                    <div class="mg-empty-state mg-empty-state--compact">
                        <i class="ri-bar-chart-box-line"></i>
                        <p>Sem dados suficientes para o relatório.</p>
                    </div>
                @else
                    @php $max = max($byMonth->max('total'), 1); @endphp
                    <div class="mg-report-bars">
                        @foreach($byMonth as $row)
                            @php
                                $label = \Carbon\Carbon::createFromFormat('Y-m', $row->month)->translatedFormat('M Y');
                                $pct = ($row->total / $max) * 100;
                            @endphp
                            <div class="mg-report-row">
                                <div class="mg-report-label">{{ $label }}</div>
                                <div class="mg-report-track"><div class="mg-report-fill" style="width: {{ $pct }}%"></div></div>
                                <div class="mg-report-value">R$ {{ number_format($row->total, 0, ',', '.') }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        <div class="col-lg-4">
            <div class="mg-panel mg-panel--compact h-100">
                <h2 class="mg-section-title h6 mb-3">Resumo analítico</h2>
                <div class="mg-list-row">
                    <div class="mg-list-body">
                        <div class="mg-list-title">Receita líquida estimada</div>
                        <div class="mg-list-sub">Total - pendências</div>
                    </div>
                    <strong>R$ {{ number_format(max($availableBalance - $pendingBalance, 0), 2, ',', '.') }}</strong>
                </div>
                <div class="mg-list-row">
                    <div class="mg-list-body">
                        <div class="mg-list-title">Representatividade do mês</div>
                        <div class="mg-list-sub">Mês atual / receita total</div>
                    </div>
                    <strong>{{ $availableBalance > 0 ? number_format(($monthRevenue / $availableBalance) * 100, 1, ',', '.') : '0,0' }}%</strong>
                </div>
                <div class="mg-list-row">
                    <div class="mg-list-body">
                        <div class="mg-list-title">Risco financeiro</div>
                        <div class="mg-list-sub">Pendências sobre o total</div>
                    </div>
                    @if($availableBalance > 0 && (($pendingBalance / $availableBalance) > 0.2))
                        <span class="mg-chip mg-chip--danger">Alto</span>
                    @else
                        <span class="mg-chip mg-chip--success">Controlado</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
