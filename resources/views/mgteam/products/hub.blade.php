@extends('layouts.master')

@section('title', 'Produtos')

@section('content')
@php
    $money = fn ($v) => 'R$ '.number_format((float) $v, 2, ',', '.');
    $typeLabels = [
        'service' => 'Serviço',
        'plan' => 'Plano',
        'digital' => 'Digital',
        'physical' => 'Físico',
    ];
@endphp

<div class="mg-clients-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Produtos</h1>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter">
                    <i class="ri-shopping-bag-3-line"></i>
                    {{ ($products ?? collect())->count() }} produtos
                </span>
            </div>
        </div>
        <div class="mg-clients-toolbar__right">
            <a href="{{ route('membership-plans.index') }}" class="mg-btn-ghost"><i class="ri-price-tag-3-line"></i> Planos</a>
            <a href="{{ route('products.coupons') }}" class="mg-btn-ghost"><i class="ri-coupon-3-line"></i> Cupons</a>
            <button type="button" class="mg-btn-primary" data-bs-toggle="modal" data-bs-target="#productModal">
                <i class="ri-add-line"></i> Novo produto
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="mg-hub-split mb-3">
        @foreach([
            ['title' => 'Meus planos', 'route' => 'membership-plans.index', 'icon' => 'ri-price-tag-3-line', 'desc' => 'Assinaturas e consultorias'],
            ['title' => 'Cupons', 'route' => 'products.coupons', 'icon' => 'ri-coupon-3-line', 'desc' => 'Descontos e promoções'],
            ['title' => 'Afiliados', 'route' => 'products.affiliates', 'icon' => 'ri-team-line', 'desc' => 'Indicações e comissões'],
            ['title' => 'Recuperação de carrinho', 'route' => 'products.cart-recovery', 'icon' => 'ri-shopping-cart-2-line', 'desc' => 'Checkout abandonado'],
        ] as $card)
            <a href="{{ route($card['route']) }}" class="mg-client-card text-decoration-none">
                <div class="mg-client-card__main">
                    <div class="mg-client-card__avatar" style="background:linear-gradient(135deg,#1d4ed8,#3b82f6)">
                        <i class="{{ $card['icon'] }}"></i>
                    </div>
                    <div class="mg-client-card__identity">
                        <div class="mg-client-card__name">{{ $card['title'] }}</div>
                        <div class="mg-client-card__meta">{{ $card['desc'] }}</div>
                    </div>
                </div>
                <div class="mg-client-card__actions">
                    <i class="ri-arrow-right-s-line mg-client-card__chevron"></i>
                </div>
            </a>
        @endforeach
    </div>

    <div class="mg-panel-label mb-2">VITRINE LOCAL</div>
    <div class="mg-client-list">
        @forelse($products ?? [] as $product)
            <div class="mg-client-card">
                <div class="mg-client-card__main">
                    <div class="mg-client-card__avatar" style="background:linear-gradient(135deg,#0f766e,#2dd4bf)">
                        <i class="ri-box-3-line"></i>
                    </div>
                    <div class="mg-client-card__identity">
                        <div class="mg-client-card__name">{{ $product->name }}</div>
                        <div class="mg-client-card__meta">
                            <span>{{ $money($product->price) }}</span>
                            <span>{{ $typeLabels[$product->type] ?? ($product->type ?: 'Serviço') }}</span>
                            @if($product->description)
                                <span>{{ \Illuminate\Support\Str::limit($product->description, 70) }}</span>
                            @endif
                        </div>
                        <div class="mg-client-chips">
                            @if($product->active)
                                <span class="mg-chip mg-chip--success">Ativo</span>
                            @else
                                <span class="mg-chip">Inativo</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="mg-empty-state">
                <i class="ri-shopping-bag-3-line"></i>
                <p>Nenhum produto cadastrado na vitrine.</p>
                <button type="button" class="mg-btn-primary" data-bs-toggle="modal" data-bs-target="#productModal">Criar produto</button>
            </div>
        @endforelse
    </div>
</div>

<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('products.quick-store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Novo produto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nome</label>
                    <input type="text" name="name" class="form-control" required placeholder="Ex: Consultoria mensal">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Preço</label>
                        <input type="number" step="0.01" min="0" name="price" class="form-control" required value="197">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tipo</label>
                        <select name="type" class="form-select">
                            <option value="service">Serviço</option>
                            <option value="plan">Plano</option>
                            <option value="digital">Digital</option>
                            <option value="physical">Físico</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Descrição</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar produto</button>
            </div>
        </form>
    </div>
</div>
@endsection
