@extends('layouts.master')

@section('title', 'Vendas')

@section('css')
<link href="{{ URL::asset('build/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="mg-page-title">Vendas e faturas</h1>
        <p class="mg-page-sub">Acompanhe receita, pagamentos e pendências.</p>
    </div>
    @can('create payments')
        <a href="{{ route('invoices.create') }}" class="btn btn-primary">
            <i class="ri-add-line me-1"></i> Nova venda
        </a>
    @endcan
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-4 col-md-6">
        <div class="mg-stat-card"><div class="card-body">
            <div class="mg-stat-label">Total faturado</div>
            <div class="mg-stat-value">R$ {{ number_format($totalAmount, 2, ',', '.') }}</div>
        </div></div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="mg-stat-card"><div class="card-body">
            <div class="mg-stat-label">Total recebido</div>
            <div class="mg-stat-value text-success">R$ {{ number_format($totalPaid, 2, ',', '.') }}</div>
        </div></div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="mg-stat-card"><div class="card-body">
            <div class="mg-stat-label">Em aberto</div>
            <div class="mg-stat-value text-danger">R$ {{ number_format($totalDue, 2, ',', '.') }}</div>
        </div></div>
    </div>
</div>

<div class="mg-panel">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form method="GET" action="{{ route('invoices.index') }}" class="row g-2 mb-3">
            <div class="col-md-3">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Buscar fatura ou cliente...">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">Todos os status</option>
                    <option value="paid" @selected(request('status') === 'paid')>Pago</option>
                    <option value="partially_paid" @selected(request('status') === 'partially_paid')>Parcial</option>
                    <option value="unpaid" @selected(request('status') === 'unpaid')>Em aberto</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control">
            </div>
            <div class="col-md-2">
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </div>
        </form>

        <div class="table-responsive">
            {!! $dataTable->table() !!}
        </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@section('script')
{!! $dataTable->scripts() !!}
@endsection
