@extends('layouts.master')

@section('title', 'Cupons')

@section('content')
<div class="mg-clients-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Cupons</h1>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter">
                    <i class="ri-coupon-3-line"></i>
                    {{ $coupons->total() }} cupons
                </span>
            </div>
        </div>
        <div class="mg-clients-toolbar__right">
            <a href="{{ route('products.hub') }}" class="mg-btn-ghost">
                <i class="ri-arrow-left-line"></i> Hub produtos
            </a>
            <a href="{{ route('membership-plans.index') }}" class="mg-btn-ghost">
                <i class="ri-price-tag-3-line"></i> Planos
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="mg-panel mg-panel--compact">
        <div class="mg-panel-label mb-3">Novo cupom</div>
        <form method="POST" action="{{ route('products.coupons.store') }}" class="mg-coupon-form">
            @csrf
            <div class="mg-coupon-form__grid">
                <div>
                    <label class="mg-field-label">Código</label>
                    <input name="code" class="mg-field" placeholder="EX: BEMVINDO10" required>
                </div>
                <div>
                    <label class="mg-field-label">Tipo</label>
                    <select name="discount_type" class="mg-field">
                        <option value="percent">Percentual (%)</option>
                        <option value="fixed">Valor fixo (R$)</option>
                    </select>
                </div>
                <div>
                    <label class="mg-field-label">Valor</label>
                    <input name="discount_value" type="number" step="0.01" class="mg-field" placeholder="0" required>
                </div>
                <div>
                    <label class="mg-field-label">Validade</label>
                    <input name="expires_at" type="date" class="mg-field">
                </div>
                <div>
                    <label class="mg-field-label">Máx. usos</label>
                    <input name="max_uses" type="number" class="mg-field" placeholder="Ilimitado">
                </div>
                <div class="mg-coupon-form__submit">
                    <button type="submit" class="mg-btn-primary w-100">
                        <i class="ri-add-line"></i> Criar
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="mg-client-list">
        @forelse($coupons as $coupon)
            <div class="mg-client-card">
                <div class="mg-client-card__main">
                    <div class="mg-client-card__avatar" style="background:linear-gradient(135deg,#7c3aed,#a78bfa)">
                        <i class="ri-coupon-3-line"></i>
                    </div>
                    <div class="mg-client-card__identity">
                        <div class="mg-client-card__name"><code class="mg-code">{{ $coupon->code }}</code></div>
                        <div class="mg-client-card__meta">
                            <span>
                                {{ $coupon->discount_type === 'percent'
                                    ? $coupon->discount_value.'%'
                                    : 'R$ '.number_format($coupon->discount_value, 2, ',', '.') }}
                            </span>
                            <span class="mg-client-card__sep">|</span>
                            <span>{{ $coupon->uses_count }}{{ $coupon->max_uses ? '/'.$coupon->max_uses : '' }} usos</span>
                            <span class="mg-client-card__sep">|</span>
                            <span>{{ $coupon->expires_at?->format('d/m/Y') ?? 'Sem validade' }}</span>
                        </div>
                        <div class="mg-client-chips">
                            @if($coupon->is_active)
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
                <i class="ri-coupon-3-line"></i>
                <p>Nenhum cupom criado ainda.</p>
            </div>
        @endforelse
    </div>

    @if($coupons->hasPages())
        <div class="mg-pagination">{{ $coupons->links() }}</div>
    @endif
</div>
@endsection
