@extends('layouts.master')

@section('title', 'Cupons')

@section('content')
<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Cupons</h1>
            <div class="prime-clients-counters">
                <span class="prime-clients-counter">
                    <i class="ri-coupon-3-line"></i>
                    {{ $coupons->total() }} cupons
                </span>
            </div>
        </div>
        <div class="prime-clients-toolbar__right">
            <a href="{{ route('products.hub') }}" class="prime-btn-ghost">
                <i class="ri-arrow-left-line"></i> Hub produtos
            </a>
            <a href="{{ route('membership-plans.index') }}" class="prime-btn-ghost">
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

    <div class="prime-panel prime-panel--compact">
        <div class="prime-panel-label mb-3">Novo cupom</div>
        <form method="POST" action="{{ route('products.coupons.store') }}" class="prime-coupon-form">
            @csrf
            <div class="prime-coupon-form__grid">
                <div>
                    <label class="prime-field-label">Código</label>
                    <input name="code" class="prime-field" placeholder="EX: BEMVINDO10" required>
                </div>
                <div>
                    <label class="prime-field-label">Tipo</label>
                    <select name="discount_type" class="prime-field">
                        <option value="percent">Percentual (%)</option>
                        <option value="fixed">Valor fixo (R$)</option>
                    </select>
                </div>
                <div>
                    <label class="prime-field-label">Valor</label>
                    <input name="discount_value" type="number" step="0.01" class="prime-field" placeholder="0" required>
                </div>
                <div>
                    <label class="prime-field-label">Validade</label>
                    <input name="expires_at" type="date" class="prime-field">
                </div>
                <div>
                    <label class="prime-field-label">Máx. usos</label>
                    <input name="max_uses" type="number" class="prime-field" placeholder="Ilimitado">
                </div>
                <div class="prime-coupon-form__submit">
                    <button type="submit" class="prime-btn-primary w-100">
                        <i class="ri-add-line"></i> Criar
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="prime-client-list">
        @forelse($coupons as $coupon)
            <div class="prime-client-card">
                <div class="prime-client-card__main">
                    <div class="prime-client-card__avatar" style="background:linear-gradient(135deg,#7c3aed,#a78bfa)">
                        <i class="ri-coupon-3-line"></i>
                    </div>
                    <div class="prime-client-card__identity">
                        <div class="prime-client-card__name"><code class="prime-code">{{ $coupon->code }}</code></div>
                        <div class="prime-client-card__meta">
                            <span>
                                {{ $coupon->discount_type === 'percent'
                                    ? $coupon->discount_value.'%'
                                    : 'R$ '.number_format($coupon->discount_value, 2, ',', '.') }}
                            </span>
                            <span class="prime-client-card__sep">|</span>
                            <span>{{ $coupon->uses_count }}{{ $coupon->max_uses ? '/'.$coupon->max_uses : '' }} usos</span>
                            <span class="prime-client-card__sep">|</span>
                            <span>{{ $coupon->expires_at?->format('d/m/Y') ?? 'Sem validade' }}</span>
                        </div>
                        <div class="prime-client-chips">
                            @if($coupon->is_active)
                                <span class="prime-chip prime-chip--success">Ativo</span>
                            @else
                                <span class="prime-chip">Inativo</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="prime-empty-state">
                <i class="ri-coupon-3-line"></i>
                <p>Nenhum cupom criado ainda.</p>
            </div>
        @endforelse
    </div>

    @if($coupons->hasPages())
        <div class="prime-pagination">{{ $coupons->links() }}</div>
    @endif
</div>
@endsection
