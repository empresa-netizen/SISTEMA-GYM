@extends('layouts.master')

@section('title', 'Alimentos')

@php $filtersOpen = true; @endphp

@push('styles')
    <style>
        .mg-foods-page {
            display: grid;
            gap: 0.85rem;
        }

        .mg-foods-form {
            padding: 0.76rem;
            border: 1px solid #DDE5EF;
            border-radius: 0.86rem;
            background: #FFFFFF;
            box-shadow: 0 10px 28px rgba(23, 37, 56, 0.04);
        }

        .mg-foods-form__grid {
            display: grid;
            grid-template-columns: minmax(12rem, 1.2fr) minmax(9rem, 0.8fr) repeat(4, 5.1rem) auto;
            gap: 0.45rem;
            align-items: end;
        }

        .mg-foods-table {
            overflow: hidden;
            border: 1px solid #D8E0EA;
            border-radius: 0.86rem;
            background: #FFFFFF;
            box-shadow: 0 8px 22px rgba(23, 37, 56, 0.04);
        }

        .mg-foods-row {
            display: grid;
            grid-template-columns: minmax(13rem, 1.35fr) minmax(8rem, 0.7fr) repeat(4, 5.6rem);
            gap: 0.5rem;
            align-items: center;
            min-height: 3.1rem;
            padding: 0.44rem 0.72rem;
            border-bottom: 1px solid #EDF1F6;
        }

        .mg-foods-row:last-child {
            border-bottom: 0;
        }

        .mg-foods-row--header {
            min-height: 2.35rem;
            background: #F6F8FB;
            color: #7A899F;
            font-size: 0.68rem;
            font-weight: 920;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .mg-foods-name {
            color: #101929;
            font-size: 0.86rem;
            font-weight: 890;
            line-height: 1.18;
        }

        .mg-foods-unit {
            margin-top: 0.12rem;
            color: #8A98AA;
            font-size: 0.7rem;
            font-weight: 720;
        }

        .mg-foods-group {
            display: inline-flex;
            width: fit-content;
            max-width: 100%;
            align-items: center;
            padding: 0.22rem 0.48rem;
            border: 1px solid #DCE4EE;
            border-radius: 999px;
            background: #F8FAFD;
            color: #5F7088;
            font-size: 0.72rem;
            font-weight: 820;
        }

        .mg-foods-macro {
            color: #23324A;
            font-size: 0.78rem;
            font-weight: 850;
            text-align: right;
        }

        .mg-foods-macro small {
            color: #8492A6;
            font-size: 0.66rem;
            font-weight: 800;
        }

        @media (max-width: 1199.98px) {
            .mg-foods-form__grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .mg-foods-form__grid > :first-child {
                grid-column: 1 / -1;
            }

            .mg-foods-row {
                grid-template-columns: minmax(13rem, 1fr) minmax(8rem, 0.7fr) repeat(2, 5.4rem);
            }

            .mg-foods-row > :nth-child(5),
            .mg-foods-row > :nth-child(6) {
                display: none;
            }
        }

        @media (max-width: 767.98px) {
            .mg-foods-form__grid,
            .mg-foods-row {
                grid-template-columns: 1fr;
            }

            .mg-foods-row--header {
                display: none;
            }

            .mg-foods-macro {
                text-align: left;
            }
        }
    </style>
@endpush

@section('content')
<div class="mg-clients-page mg-foods-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Meus alimentos</h1>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter">
                    <i class="ri-apple-line"></i>
                    {{ $foods->total() }} itens
                </span>
            </div>
        </div>
        <div class="mg-clients-toolbar__right">
            <a href="{{ route('library.diet.index') }}" class="mg-btn-ghost">
                <i class="ri-arrow-left-line"></i> Voltar
            </a>
        </div>
    </div>

    <div class="mg-clients-filters">
        <button type="button" class="mg-btn-ghost mg-filters-toggle {{ $filtersOpen ? 'is-open' : '' }}" data-bs-toggle="collapse" data-bs-target="#mgFoodsFilters" aria-expanded="{{ $filtersOpen ? 'true' : 'false' }}">
            <i class="ri-filter-3-line"></i> Filtros
            <i class="ri-arrow-down-s-line mg-filters-chevron"></i>
        </button>
        <div class="collapse {{ $filtersOpen ? 'show' : '' }}" id="mgFoodsFilters">
            <form method="GET" class="mg-clients-filters__form">
                <div class="mg-clients-filters__grid mg-clients-filters__grid--3">
                    <div>
                        <label class="mg-field-label">Buscar</label>
                        <input type="search" name="q" value="{{ request('q') }}" class="mg-field" placeholder="Nome do alimento...">
                    </div>
                    <div>
                        <label class="mg-field-label">Grupo</label>
                        <select name="group" class="mg-field">
                            <option value="">Todos os grupos</option>
                            @foreach($groups as $group)
                                <option value="{{ $group }}" @selected(request('group') === $group)>{{ $group }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mg-clients-filters__actions">
                        <button class="mg-btn-primary" type="submit">Aplicar</button>
                        <a href="{{ route('library.diet.foods') }}" class="mg-btn-ghost">Limpar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <form method="POST" action="{{ route('library.diet.foods.store') }}" class="mg-foods-form">
        @csrf
        <div class="mg-foods-form__grid">
            <div>
                <label class="mg-field-label">Nome</label>
                <input name="name" class="mg-field" value="{{ old('name') }}" placeholder="Arroz branco cozido" required>
            </div>
            <div>
                <label class="mg-field-label">Grupo</label>
                <input name="food_group" class="mg-field" value="{{ old('food_group') }}" placeholder="Carboidratos">
            </div>
            <div>
                <label class="mg-field-label">kcal/100g</label>
                <input name="calories" type="number" step="0.1" class="mg-field" value="{{ old('calories') }}" placeholder="0">
            </div>
            <div>
                <label class="mg-field-label">Proteína</label>
                <input name="protein" type="number" step="0.1" class="mg-field" value="{{ old('protein') }}" placeholder="0">
            </div>
            <div>
                <label class="mg-field-label">Carbo</label>
                <input name="carbs" type="number" step="0.1" class="mg-field" value="{{ old('carbs') }}" placeholder="0">
            </div>
            <div>
                <label class="mg-field-label">Gordura</label>
                <input name="fat" type="number" step="0.1" class="mg-field" value="{{ old('fat') }}" placeholder="0">
            </div>
            <div>
                <button class="mg-btn-primary w-100" type="submit"><i class="ri-add-line"></i> Add</button>
            </div>
        </div>
    </form>

    <div class="mg-foods-table">
        <div class="mg-foods-row mg-foods-row--header">
            <span>Alimento</span>
            <span>Grupo</span>
            <span class="text-end">kcal</span>
            <span class="text-end">Proteína</span>
            <span class="text-end">Carbo</span>
            <span class="text-end">Gordura</span>
        </div>
        @forelse($foods as $food)
            <div class="mg-foods-row">
                <div>
                    <div class="mg-foods-name">{{ $food->name }}</div>
                    <div class="mg-foods-unit">{{ $food->unit }} · valores por 100g</div>
                </div>
                <div>
                    <span class="mg-foods-group">{{ $food->food_group ?: 'Sem grupo' }}</span>
                </div>
                <div class="mg-foods-macro">{{ number_format($food->calories, 0, ',', '.') }} <small>kcal</small></div>
                <div class="mg-foods-macro">{{ number_format($food->protein, 1, ',', '.') }} <small>g</small></div>
                <div class="mg-foods-macro">{{ number_format($food->carbs, 1, ',', '.') }} <small>g</small></div>
                <div class="mg-foods-macro">{{ number_format($food->fat, 1, ',', '.') }} <small>g</small></div>
            </div>
        @empty
            <div class="mg-empty-state">
                <i class="ri-apple-line"></i>
                <p>Nenhum alimento cadastrado.</p>
            </div>
        @endforelse
    </div>

    @if($foods->hasPages())
        <div class="mg-pagination">{{ $foods->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
