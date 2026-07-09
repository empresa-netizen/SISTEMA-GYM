@extends('layouts.master')

@section('title', 'Categorias')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2 prime-page-header">
    <div>
        <h1 class="prime-page-title">Categorias</h1>
        <p class="prime-section-label mb-1">Organize modalidades e serviços da {{ config('brand.name', 'MGTEAM FITNESS & HEALTH') }}.</p>
    </div>
    <a href="{{ route('categories.create') }}" class="prime-btn">
        <i class="ri-add-line align-middle me-1"></i> Nova categoria
    </a>
</div>

<div class="prime-panel">
    <div class="prime-panel-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ri-check-line align-middle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

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
