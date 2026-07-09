@extends('layouts.master')

@section('title', $product->name)

@section('content')
@php
    $unitLabels = [
        'piece' => 'Unidade',
        'kg' => 'Quilograma',
        'liter' => 'Litro',
        'box' => 'Caixa',
        'bottle' => 'Garrafa',
    ];
    $unitLabel = $unitLabels[$product->unit] ?? $product->unit;
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="prime-page-title">{{ $product->name }}</h1>
        <p class="prime-page-sub">{{ $product->product_id }} · {{ $product->category?->name ?? 'Sem categoria' }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('products.edit', $product) }}" class="btn btn-primary btn-sm"><i class="ri-pencil-line me-1"></i> Editar</a>
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm"><i class="ri-arrow-left-line"></i></a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="prime-panel text-center">
            @if($product->image)
                <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="img-fluid rounded mb-3" style="max-height:200px;object-fit:cover">
            @else
                <div class="text-muted py-4 mb-3"><i class="ri-image-line fs-1 opacity-50"></i></div>
            @endif
            <div class="prime-panel-value prime-panel-value--sm mb-2">R$ {{ number_format($product->price, 2, ',', '.') }}</div>
            @if($product->active)
                <span class="badge bg-success-subtle text-success">Ativo</span>
            @else
                <span class="badge bg-danger-subtle text-danger">Inativo</span>
            @endif
            @if($product->isLowStock())
                <p class="small text-warning mt-3 mb-0"><i class="ri-alert-line me-1"></i> Estoque baixo</p>
            @endif
        </div>
    </div>
    <div class="col-lg-8">
        <div class="row g-2 mb-3">
            <div class="col-6 col-md-3">
                <div class="prime-stat-mini">
                    <span>Estoque</span>
                    <strong>{{ $product->stock_quantity }} {{ $unitLabel }}</strong>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="prime-stat-mini">
                    <span>Mínimo</span>
                    <strong>{{ $product->min_stock_level }}</strong>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="prime-stat-mini">
                    <span>Custo</span>
                    <strong>R$ {{ number_format($product->cost_price ?? 0, 2, ',', '.') }}</strong>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="prime-stat-mini">
                    <span>SKU</span>
                    <strong class="fw-normal small">{{ $product->sku ?? '—' }}</strong>
                </div>
            </div>
        </div>

        <div class="prime-panel mb-3" style="height:auto">
            <div class="prime-panel-label mb-3">DETALHES</div>
            <dl class="prime-detail-grid mb-0">
                <dt>Nome</dt><dd>{{ $product->name }}</dd>
                <dt>Categoria</dt><dd>{{ $product->category?->name ?? '—' }}</dd>
                <dt>Unidade</dt><dd>{{ $unitLabel }}</dd>
                <dt>Controle de estoque</dt><dd>{{ $product->track_inventory ? 'Sim' : 'Não' }}</dd>
                <dt>Descrição</dt><dd>{{ $product->description ?? '—' }}</dd>
            </dl>
        </div>

        <div class="prime-panel" style="height:auto">
            <div class="prime-panel-label mb-3">ÚLTIMAS VENDAS</div>
            @forelse($product->sales as $sale)
                <div class="prime-list-row">
                    <div class="prime-list-body">
                        <div class="prime-list-title">{{ $sale->sale_id ?? 'Venda #'.$sale->id }}</div>
                        <div class="prime-list-sub">
                            {{ $sale->sale_date?->format('d/m/Y H:i') ?? '—' }}
                            · {{ $sale->quantity }} {{ $unitLabel }}
                            @if($sale->member) · {{ $sale->member->name }} @endif
                        </div>
                    </div>
                    <strong class="small">R$ {{ number_format($sale->final_amount, 2, ',', '.') }}</strong>
                </div>
            @empty
                <div class="text-center text-muted py-4">
                    <i class="ri-shopping-bag-line fs-3 d-block mb-2 opacity-50"></i>
                    <p class="small mb-0">Nenhuma venda registrada.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
