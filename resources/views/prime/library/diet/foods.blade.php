@extends('layouts.master')

@section('title', 'Alimentos')

@section('content')
@php $filtersOpen = request()->hasAny(['q', 'group']); @endphp

<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Meus alimentos</h1>
            <div class="prime-clients-counters">
                <span class="prime-clients-counter">
                    <i class="ri-apple-line"></i>
                    {{ $foods->total() }} itens
                </span>
            </div>
        </div>
        <div class="prime-clients-toolbar__right">
            <a href="{{ route('library.diet.index') }}" class="prime-btn-ghost">
                <i class="ri-arrow-left-line"></i> Voltar
            </a>
        </div>
    </div>

    <div class="prime-clients-filters">
        <button type="button" class="prime-btn-ghost prime-filters-toggle {{ $filtersOpen ? 'is-open' : '' }}" data-bs-toggle="collapse" data-bs-target="#primeFoodsFilters" aria-expanded="{{ $filtersOpen ? 'true' : 'false' }}">
            <i class="ri-filter-3-line"></i> Filtros
            <i class="ri-arrow-down-s-line prime-filters-chevron"></i>
        </button>
        <div class="collapse {{ $filtersOpen ? 'show' : '' }}" id="primeFoodsFilters">
            <form method="GET" class="prime-clients-filters__form">
                <div class="prime-clients-filters__grid prime-clients-filters__grid--3">
                    <div>
                        <label class="prime-field-label">Buscar</label>
                        <input type="search" name="q" value="{{ request('q') }}" class="prime-field" placeholder="Nome do alimento...">
                    </div>
                    <div>
                        <label class="prime-field-label">Grupo</label>
                        <select name="group" class="prime-field">
                            <option value="">Todos os grupos</option>
                            @foreach($groups as $g)
                                <option value="{{ $g }}" @selected(request('group') === $g)>{{ $g }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="prime-clients-filters__actions">
                        <button class="prime-btn-primary" type="submit">Aplicar</button>
                        <a href="{{ route('library.diet.foods') }}" class="prime-btn-ghost">Limpar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="prime-panel prime-panel--compact">
        <div class="prime-panel-label mb-3">Adicionar alimento</div>
        <form method="POST" action="{{ route('library.diet.foods.store') }}" class="prime-coupon-form">
            @csrf
            <div class="prime-coupon-form__grid" style="grid-template-columns: 1.4fr 1fr repeat(4, 0.7fr) auto;">
                <div>
                    <label class="prime-field-label">Nome</label>
                    <input name="name" class="prime-field" placeholder="Nome" required>
                </div>
                <div>
                    <label class="prime-field-label">Grupo</label>
                    <input name="food_group" class="prime-field" placeholder="Grupo">
                </div>
                <div>
                    <label class="prime-field-label">kcal</label>
                    <input name="calories" type="number" step="0.1" class="prime-field" placeholder="0">
                </div>
                <div>
                    <label class="prime-field-label">P</label>
                    <input name="protein" type="number" step="0.1" class="prime-field" placeholder="0">
                </div>
                <div>
                    <label class="prime-field-label">C</label>
                    <input name="carbs" type="number" step="0.1" class="prime-field" placeholder="0">
                </div>
                <div>
                    <label class="prime-field-label">G</label>
                    <input name="fat" type="number" step="0.1" class="prime-field" placeholder="0">
                </div>
                <div class="prime-coupon-form__submit">
                    <button class="prime-btn-primary" type="submit"><i class="ri-add-line"></i> Add</button>
                </div>
            </div>
        </form>
    </div>

    <div class="prime-client-list">
        @forelse($foods as $food)
            <div class="prime-client-card">
                <div class="prime-client-card__main">
                    <div class="prime-client-card__avatar" style="background:linear-gradient(135deg,#15803d,#22c55e)">
                        <i class="ri-apple-line"></i>
                    </div>
                    <div class="prime-client-card__identity">
                        <div class="prime-client-card__name">{{ $food->name }}</div>
                        <div class="prime-client-card__meta">
                            <span>{{ $food->food_group ?? 'Sem grupo' }}</span>
                            <span class="prime-client-card__sep">|</span>
                            <span>{{ number_format($food->calories, 0) }} kcal</span>
                            <span class="prime-client-card__sep">|</span>
                            <span>P {{ number_format($food->protein, 1) }}</span>
                            <span class="prime-client-card__sep">|</span>
                            <span>C {{ number_format($food->carbs, 1) }}</span>
                            <span class="prime-client-card__sep">|</span>
                            <span>G {{ number_format($food->fat, 1) }}</span>
                        </div>
                        <div class="prime-client-chips">
                            <span class="prime-chip">{{ $food->unit }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="prime-empty-state">
                <i class="ri-apple-line"></i>
                <p>Nenhum alimento cadastrado.</p>
            </div>
        @endforelse
    </div>

    @if($foods->hasPages())
        <div class="prime-pagination">{{ $foods->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
