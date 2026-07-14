@extends('layouts.master')

@section('title', 'Relatório de frequência')

@section('css')
<link href="{{ URL::asset('build/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="mg-page-title">Relatório de frequência</h1>
        <p class="mg-page-sub">Análise de visitas e permanência na academia.</p>
    </div>
    <a href="{{ route('attendances.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="ri-arrow-left-line me-1"></i> Voltar
    </a>
</div>

<div class="row g-2 mb-4">
    <div class="col-md-3">
        <div class="mg-stat-mini">
            <span>Total de visitas</span>
            <strong>{{ $stats['total_visits'] }}</strong>
        </div>
    </div>
    <div class="col-md-3">
        <div class="mg-stat-mini">
            <span>Clientes únicos</span>
            <strong>{{ $stats['unique_members'] }}</strong>
        </div>
    </div>
    <div class="col-md-3">
        <div class="mg-stat-mini">
            <span>Duração média</span>
            <strong>{{ $stats['avg_duration'] }} min</strong>
        </div>
    </div>
    <div class="col-md-3">
        <div class="mg-stat-mini">
            <span>Total de horas</span>
            <strong>{{ $stats['total_hours'] }}h</strong>
        </div>
    </div>
</div>

<div class="mg-panel" style="height:auto">
    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-4">
            <label class="form-label">Data inicial</label>
            <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Data final</label>
            <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">&nbsp;</label>
            <button type="submit" class="btn btn-primary w-100">
                <i class="ri-filter-line me-1"></i> Gerar relatório
            </button>
        </div>
    </form>

    <div class="table-responsive">
        {!! $dataTable->table() !!}
    </div>
</div>
@endsection

@section('script')
{!! $dataTable->scripts() !!}
@endsection
