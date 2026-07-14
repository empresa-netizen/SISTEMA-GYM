@extends('layouts.master')

@section('title', 'Treinadores')

@section('css')
<link href="{{ URL::asset('build/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="mg-page-title">Treinadores</h1>
        <p class="mg-page-sub">Gerencie a equipe de profissionais da academia.</p>
    </div>
    @can('create trainers')
        <a href="{{ route('trainers.create') }}" class="btn btn-primary">
            <i class="ri-add-line me-1"></i> Novo treinador
        </a>
    @endcan
</div>

<div class="mg-panel" style="height:auto">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="get" action="{{ route('trainers.index') }}">
        <div class="row g-2 mb-3">
            <div class="col-md-3">
                <input type="text" name="search_value" class="form-control" placeholder="Buscar treinadores...">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Todos os status</option>
                    <option value="active">Ativo</option>
                    <option value="inactive">Inativo</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </div>
            <div class="col-md-3">
                <a href="{{ route('trainers.index') }}" class="btn btn-light w-100">
                    <i class="ri-refresh-line me-1"></i> Limpar
                </a>
            </div>
        </div>

        <div class="table-responsive">
            {!! $dataTable->table() !!}
        </div>
    </form>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@section('script')
{!! $dataTable->scripts() !!}
@endsection
