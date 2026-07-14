@extends('layouts.master')

@section('title', 'Tipos Financeiros')

@section('css')
<link href="{{ URL::asset('build/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2 mg-page-header">
    <div>
        <h1 class="mg-page-title">Tipos Financeiros</h1>
        <p class="mg-page-sub">Organize categorias de receita e despesa da MGTEAM FITNESS &amp; HEALTH.</p>
    </div>
    <a href="{{ route('types.create') }}" class="btn btn-primary">
        <i class="ri-add-line align-middle me-1"></i> Novo tipo
    </a>
</div>

<div class="mg-panel">
    <div class="mg-panel-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ri-check-line align-middle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ri-error-warning-line align-middle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form method="get" action="{{ route('types.index') }}" class="row mb-3 g-2">
            <div class="col-md-8">
                <select name="category" class="form-select">
                    <option value="">Todas as categorias</option>
                    <option value="income" @selected(request('category') === 'income')>Receita</option>
                    <option value="expense" @selected(request('category') === 'expense')>Despesa</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('types.index') }}" class="btn btn-light w-100">
                    <i class="ri-refresh-line"></i> Limpar
                </a>
            </div>
        </form>

        <div class="table-responsive">
            {!! $dataTable->table() !!}
        </div>
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
