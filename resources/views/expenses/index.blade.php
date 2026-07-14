@extends('layouts.master')

@section('title', 'Despesas')

@section('css')
<link href="{{ URL::asset('build/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="mg-page-title">Despesas</h1>
        <p class="mg-page-sub">Controle de gastos e saídas financeiras.</p>
    </div>
    <a href="{{ route('expenses.create') }}" class="btn btn-primary">
        <i class="ri-add-line me-1"></i> Nova despesa
    </a>
</div>

<div class="row g-2 mb-4">
    <div class="col-md-12">
        <div class="mg-stat-mini">
            <span>Total de despesas</span>
            <strong class="text-danger">R$ {{ number_format($totalExpenses, 2, ',', '.') }}</strong>
        </div>
    </div>
</div>

<div class="mg-panel" style="height:auto">
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

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-4">
            <select name="type" class="form-select">
                <option value="">Todos os tipos</option>
                @foreach($types as $type)
                    <option value="{{ $type->id }}" {{ request('type') == $type->id ? 'selected' : '' }}>
                        {{ $type->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <input type="date" name="start_date" class="form-control"
                   value="{{ request('start_date') }}" placeholder="Data inicial">
        </div>
        <div class="col-md-3">
            <input type="date" name="end_date" class="form-control"
                   value="{{ request('end_date') }}" placeholder="Data final">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">
                <i class="ri-filter-line me-1"></i> Filtrar
            </button>
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
