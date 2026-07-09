@extends('layouts.master')

@section('title', 'Cardápios')

@section('content')
<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Meus cardápios</h1>
            <div class="prime-clients-counters">
                <span class="prime-clients-counter">
                    <i class="ri-restaurant-line"></i>
                    {{ $menus->total() }} cardápios
                </span>
            </div>
        </div>
        <div class="prime-clients-toolbar__right">
            <a href="{{ route('library.diet.index') }}" class="prime-btn-ghost">
                <i class="ri-arrow-left-line"></i> Voltar
            </a>
        </div>
    </div>

    <div class="prime-panel prime-panel--compact">
        <div class="prime-panel-label mb-3">Novo cardápio</div>
        <form method="POST" action="{{ route('library.diet.menus.store') }}" class="prime-coupon-form">
            @csrf
            <div class="prime-coupon-form__grid" style="grid-template-columns: 1.4fr 0.8fr 0.8fr 1fr auto;">
                <div>
                    <label class="prime-field-label">Nome</label>
                    <input name="name" class="prime-field" placeholder="Nome do cardápio" required>
                </div>
                <div>
                    <label class="prime-field-label">Refeições</label>
                    <input name="meals_count" type="number" class="prime-field" placeholder="0" min="0">
                </div>
                <div>
                    <label class="prime-field-label">kcal total</label>
                    <input name="total_calories" type="number" class="prime-field" placeholder="0">
                </div>
                <div>
                    <label class="prime-field-label">Status</label>
                    <select name="status" class="prime-field">
                        <option value="draft">Rascunho</option>
                        <option value="published">Publicado</option>
                    </select>
                </div>
                <div class="prime-coupon-form__submit">
                    <button class="prime-btn-primary" type="submit"><i class="ri-add-line"></i> Criar</button>
                </div>
            </div>
        </form>
    </div>

    <div class="prime-client-list">
        @forelse($menus as $menu)
            <div class="prime-client-card">
                <div class="prime-client-card__main">
                    <div class="prime-client-card__avatar" style="background:linear-gradient(135deg,#b45309,#f59e0b)">
                        <i class="ri-restaurant-line"></i>
                    </div>
                    <div class="prime-client-card__identity">
                        <div class="prime-client-card__name">{{ $menu->name }}</div>
                        <div class="prime-client-card__meta">
                            <span>{{ $menu->meals_count }} refeições</span>
                            <span class="prime-client-card__sep">|</span>
                            <span>{{ number_format($menu->total_calories, 0) }} kcal</span>
                            @if($menu->description)
                                <span class="prime-client-card__sep">|</span>
                                <span>{{ \Illuminate\Support\Str::limit($menu->description, 60) }}</span>
                            @endif
                        </div>
                        <div class="prime-client-chips">
                            @if($menu->status === 'published')
                                <span class="prime-chip prime-chip--success">Publicado</span>
                            @else
                                <span class="prime-chip">Rascunho</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="prime-empty-state">
                <i class="ri-restaurant-line"></i>
                <p>Nenhum cardápio criado ainda.</p>
            </div>
        @endforelse
    </div>

    @if($menus->hasPages())
        <div class="prime-pagination">{{ $menus->links() }}</div>
    @endif
</div>
@endsection
