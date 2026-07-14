@extends('layouts.master')

@section('title', 'Alimentos')

@php $filtersOpen = true; @endphp

@push('styles')
    <style>
        .prime-foods-page {
            display: grid;
            gap: 0.85rem;
        }

        .prime-foods-form {
            padding: 0.76rem;
            border: 1px solid #DDE5EF;
            border-radius: 0.86rem;
            background: #FFFFFF;
            box-shadow: 0 10px 28px rgba(23, 37, 56, 0.04);
        }

        .prime-foods-form__grid {
            display: grid;
            grid-template-columns: minmax(12rem, 1.2fr) minmax(9rem, 0.8fr) repeat(4, 5.1rem) auto;
            gap: 0.45rem;
            align-items: end;
        }

        .prime-foods-table {
            overflow: hidden;
            border: 1px solid #D8E0EA;
            border-radius: 0.86rem;
            background: #FFFFFF;
            box-shadow: 0 8px 22px rgba(23, 37, 56, 0.04);
        }

        .prime-foods-row {
            display: grid;
            grid-template-columns: minmax(13rem, 1.35fr) minmax(8rem, 0.7fr) repeat(4, 5.6rem);
            gap: 0.5rem;
            align-items: center;
            min-height: 3.1rem;
            padding: 0.44rem 0.72rem;
            border-bottom: 1px solid #EDF1F6;
        }

        .prime-foods-row:last-child {
            border-bottom: 0;
        }

        .prime-foods-row--header {
            min-height: 2.35rem;
            background: #F6F8FB;
            color: #7A899F;
            font-size: 0.68rem;
            font-weight: 920;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .prime-foods-name {
            color: #101929;
            font-size: 0.86rem;
            font-weight: 890;
            line-height: 1.18;
        }

        .prime-foods-unit {
            margin-top: 0.12rem;
            color: #8A98AA;
            font-size: 0.7rem;
            font-weight: 720;
        }

        .prime-foods-group {
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

        .prime-foods-macro {
            color: #23324A;
            font-size: 0.78rem;
            font-weight: 850;
            text-align: right;
        }

        .prime-foods-macro small {
            color: #8492A6;
            font-size: 0.66rem;
            font-weight: 800;
        }

        @media (max-width: 1199.98px) {
            .prime-foods-form__grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .prime-foods-form__grid > :first-child {
                grid-column: 1 / -1;
            }

            .prime-foods-row {
                grid-template-columns: minmax(13rem, 1fr) minmax(8rem, 0.7fr) repeat(2, 5.4rem);
            }

            .prime-foods-row > :nth-child(5),
            .prime-foods-row > :nth-child(6) {
                display: none;
            }
        }

        @media (max-width: 767.98px) {
            .prime-foods-form__grid,
            .prime-foods-row {
                grid-template-columns: 1fr;
            }

            .prime-foods-row--header {
                display: none;
            }

            .prime-foods-macro {
                text-align: left;
            }
        }
    </style>
@endpush

@section('content')
<div class="prime-clients-page prime-foods-page">
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
                            @foreach($groups as $group)
                                <option value="{{ $group }}" @selected(request('group') === $group)>{{ $group }}</option>
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

    <form method="POST" action="{{ route('library.diet.foods.store') }}" class="prime-foods-form">
        @csrf
        <div class="prime-foods-form__grid">
            <div>
                <label class="prime-field-label">Nome</label>
                <input name="name" class="prime-field" value="{{ old('name') }}" placeholder="Arroz branco cozido" required>
            </div>
            <div>
                <label class="prime-field-label">Grupo</label>
                <input name="food_group" class="prime-field" value="{{ old('food_group') }}" placeholder="Carboidratos">
            </div>
            <div>
                <label class="prime-field-label">kcal/100g</label>
                <input name="calories" type="number" step="0.1" class="prime-field" value="{{ old('calories') }}" placeholder="0">
            </div>
            <div>
                <label class="prime-field-label">Proteína</label>
                <input name="protein" type="number" step="0.1" class="prime-field" value="{{ old('protein') }}" placeholder="0">
            </div>
            <div>
                <label class="prime-field-label">Carbo</label>
                <input name="carbs" type="number" step="0.1" class="prime-field" value="{{ old('carbs') }}" placeholder="0">
            </div>
            <div>
                <label class="prime-field-label">Gordura</label>
                <input name="fat" type="number" step="0.1" class="prime-field" value="{{ old('fat') }}" placeholder="0">
            </div>
            <div>
                <button class="prime-btn-primary w-100" type="submit"><i class="ri-add-line"></i> Add</button>
            </div>
        </div>
    </form>

    <div class="prime-foods-table">
        <div class="prime-foods-row prime-foods-row--header">
            <span>Alimento</span>
            <span>Grupo</span>
            <span class="text-end">kcal</span>
            <span class="text-end">Proteína</span>
            <span class="text-end">Carbo</span>
            <span class="text-end">Gordura</span>
        </div>
        @forelse($foods as $food)
            <div class="prime-foods-row">
                <div>
                    <div class="prime-foods-name">{{ $food->name }}</div>
                    <div class="prime-foods-unit">{{ $food->unit }} · valores por 100g</div>
                </div>
                <div>
                    <span class="prime-foods-group">{{ $food->food_group ?: 'Sem grupo' }}</span>
                </div>
                <div class="prime-foods-macro">{{ number_format($food->calories, 0, ',', '.') }} <small>kcal</small></div>
                <div class="prime-foods-macro">{{ number_format($food->protein, 1, ',', '.') }} <small>g</small></div>
                <div class="prime-foods-macro">{{ number_format($food->carbs, 1, ',', '.') }} <small>g</small></div>
                <div class="prime-foods-macro">{{ number_format($food->fat, 1, ',', '.') }} <small>g</small></div>
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
