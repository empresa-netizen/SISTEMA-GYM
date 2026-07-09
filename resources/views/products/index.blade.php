@extends('layouts.master')

@section('title', 'Produtos')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2 prime-page-header">
    <div>
        <h1 class="prime-page-title">Produtos</h1>
        <p class="prime-section-label mb-1">Gestão de estoque e catálogo da {{ config('brand.name', 'MGTEAM FITNESS & HEALTH') }}.</p>
    </div>
    <a href="{{ route('products.create') }}" class="prime-btn">
        <i class="ri-add-line align-middle me-1"></i> Novo produto
    </a>
</div>

<div class="prime-panel">
    <div class="prime-panel-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ri-check-line me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ri-error-warning-line me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-responsive">
            {!! $dataTable->table() !!}
        </div>
    </div>
</div>
@endsection

@section('script')
{!! $dataTable->scripts() !!}
@endsection