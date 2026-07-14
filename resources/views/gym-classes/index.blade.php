@extends('layouts.master')

@section('title', 'Aulas')

@section('css')
<link href="{{ URL::asset('build/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="mg-page-title">Aulas</h1>
        <p class="mg-page-sub">Gerencie turmas, horários e capacidade.</p>
    </div>
    <a href="{{ route('gym-classes.create') }}" class="btn btn-primary">
        <i class="ri-add-line me-1"></i> Nova aula
    </a>
</div>

<div class="mg-panel" style="height:auto">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('gym-classes.index') }}" method="get" class="row g-2 mb-3">
        <div class="col-md-2">
            <input type="text" name="search_value" class="form-control" placeholder="Buscar aulas...">
        </div>
        <div class="col-md-3">
            <select name="category" class="form-select">
                <option value="">Todas as categorias</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">Todos os status</option>
                <option value="active">Ativa</option>
                <option value="inactive">Inativa</option>
                <option value="cancelled">Cancelada</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
        </div>
        <div class="col-md-2">
            <a href="{{ route('gym-classes.index') }}" class="btn btn-light w-100">
                <i class="ri-refresh-line me-1"></i> Limpar
            </a>
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
