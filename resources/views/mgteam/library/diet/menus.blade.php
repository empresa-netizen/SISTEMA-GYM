@extends('layouts.master')

@section('title', 'Cardápios')

@php
    $dietFoodOptions = $foods->map(fn ($food) => [
        'id' => $food->id,
        'name' => $food->name,
        'group' => $food->food_group,
        'calories' => (float) $food->calories,
        'protein' => (float) $food->protein,
        'carbs' => (float) $food->carbs,
        'fat' => (float) $food->fat,
        'label' => trim($food->name.' · '.number_format((float) $food->calories, 0, ',', '.').' kcal/100g'),
    ])->values();
@endphp

@push('styles')
    <style>
        .mg-menu-page {
            display: grid;
            gap: 0.85rem;
        }

        .mg-menu-builder {
            padding: 0.82rem;
            border: 1px solid #DDE5EF;
            border-radius: 0.9rem;
            background: #FFFFFF;
            box-shadow: 0 10px 28px rgba(23, 37, 56, 0.045);
        }

        .mg-menu-builder__top {
            display: grid;
            grid-template-columns: minmax(12rem, 1.15fr) minmax(10rem, 0.8fr) 8rem 7rem auto;
            gap: 0.5rem;
            align-items: end;
        }

        .mg-menu-metrics {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.5rem;
            margin: 0.72rem 0;
        }

        .mg-menu-metric {
            padding: 0.58rem 0.68rem;
            border: 1px solid #DCE5EF;
            border-radius: 0.75rem;
            background: #F7FAFD;
        }

        .mg-menu-metric__label {
            color: #7C8BA0;
            font-size: 0.68rem;
            font-weight: 900;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .mg-menu-metric__value {
            margin-top: 0.18rem;
            color: #101929;
            font-size: 1rem;
            font-weight: 920;
        }

        .mg-menu-metric--protein {
            border-color: rgba(59, 149, 255, 0.26);
            background: rgba(59, 149, 255, 0.08);
        }

        .mg-menu-metric--carbs {
            border-color: rgba(245, 158, 11, 0.26);
            background: rgba(245, 158, 11, 0.08);
        }

        .mg-menu-metric--fat {
            border-color: rgba(16, 185, 129, 0.26);
            background: rgba(16, 185, 129, 0.08);
        }

        .mg-menu-meals {
            display: grid;
            gap: 0.52rem;
        }

        .mg-menu-meal {
            padding: 0.62rem;
            border: 1px solid #DDE5EF;
            border-radius: 0.82rem;
            background: #FBFCFE;
        }

        .mg-menu-meal__head {
            display: grid;
            grid-template-columns: minmax(12rem, 1fr) 7rem minmax(12rem, 0.95fr) auto auto;
            gap: 0.44rem;
            align-items: end;
            margin-bottom: 0.48rem;
        }

        .mg-menu-total-chip {
            display: inline-flex;
            min-height: 2.1rem;
            align-items: center;
            justify-content: center;
            padding: 0 0.68rem;
            border-radius: 999px;
            background: #EEF5FF;
            color: #246EC8;
            font-size: 0.72rem;
            font-weight: 900;
            white-space: nowrap;
        }

        .mg-menu-foods {
            display: grid;
            gap: 0.38rem;
        }

        .mg-menu-food-row {
            display: grid;
            grid-template-columns: minmax(13rem, 1.2fr) 6.5rem minmax(11rem, 0.8fr) minmax(11rem, 0.8fr) 2.25rem;
            gap: 0.4rem;
            align-items: center;
        }

        .mg-menu-row-macros {
            color: #586A83;
            font-size: 0.72rem;
            font-weight: 820;
            line-height: 1.25;
        }

        .mg-menu-list {
            display: grid;
            gap: 0.62rem;
        }

        .mg-menu-card {
            padding: 0.78rem 0.92rem;
            border: 1px solid #D8E0EA;
            border-radius: 0.8rem;
            background: #FFFFFF;
            box-shadow: 0 8px 22px rgba(23, 37, 56, 0.04);
        }

        .mg-menu-card__head {
            display: flex;
            justify-content: space-between;
            gap: 0.8rem;
            align-items: flex-start;
        }

        .mg-menu-card__title {
            color: #101929;
            font-size: 0.96rem;
            font-weight: 900;
            line-height: 1.16;
        }

        .mg-menu-card__meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.32rem 0.58rem;
            margin-top: 0.22rem;
            color: #6F7F96;
            font-size: 0.76rem;
            font-weight: 720;
        }

        .mg-menu-card__meals {
            display: grid;
            gap: 0.36rem;
            margin-top: 0.66rem;
        }

        .mg-menu-card__meal {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 0.72rem;
            padding: 0.48rem 0.56rem;
            border: 1px solid #E4EAF2;
            border-radius: 0.64rem;
            background: #F8FAFD;
        }

        .mg-menu-card__meal-title {
            color: #1A2840;
            font-size: 0.78rem;
            font-weight: 880;
        }

        .mg-menu-card__meal-foods {
            overflow: hidden;
            color: #7C8BA0;
            font-size: 0.7rem;
            font-weight: 650;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        @media (max-width: 1199.98px) {
            .mg-menu-builder__top,
            .mg-menu-meal__head,
            .mg-menu-food-row {
                grid-template-columns: 1fr 1fr;
            }

            .mg-menu-builder__top > :first-child,
            .mg-menu-builder__top > :nth-child(2),
            .mg-menu-food-row > :first-child,
            .mg-menu-food-row > :nth-child(4) {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 767.98px) {
            .mg-menu-metrics,
            .mg-menu-builder__top,
            .mg-menu-meal__head,
            .mg-menu-food-row,
            .mg-menu-card__head,
            .mg-menu-card__meal {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
<div class="mg-clients-page mg-menu-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Meus cardápios</h1>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter">
                    <i class="ri-restaurant-line"></i>
                    {{ $menus->total() }} cardápios
                </span>
            </div>
        </div>
        <div class="mg-clients-toolbar__right">
            <a href="{{ route('library.diet.index') }}" class="mg-btn-ghost">
                <i class="ri-arrow-left-line"></i> Voltar
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('library.diet.menus.store') }}" class="mg-menu-builder" data-menu-builder-form>
        @csrf
        <input type="hidden" name="meals_count" value="{{ old('meals_count', 0) }}" data-menu-meals-count-input>
        <input type="hidden" name="total_calories" value="{{ old('total_calories', 0) }}" data-menu-total-calories-input>

        <div class="mg-menu-builder__top">
            <div>
                <label class="mg-field-label">Nome do cardápio</label>
                <input name="name" class="mg-field" value="{{ old('name') }}" placeholder="Ex: Hipertrofia 2.800 kcal" required>
            </div>
            <div>
                <label class="mg-field-label">Descrição</label>
                <input name="description" class="mg-field" value="{{ old('description') }}" placeholder="Objetivo, restrições, observações">
            </div>
            <div>
                <label class="mg-field-label">Status</label>
                <select name="status" class="mg-field">
                    <option value="draft" @selected(old('status') === 'draft')>Rascunho</option>
                    <option value="published" @selected(old('status', 'published') === 'published')>Publicado</option>
                </select>
            </div>
            <div>
                <label class="mg-field-label">Refeições</label>
                <div class="mg-menu-total-chip"><span data-menu-meals-count>0</span> itens</div>
            </div>
            <div>
                <button class="mg-btn-primary w-100" type="submit">
                    <i class="ri-save-line"></i> Salvar
                </button>
            </div>
        </div>

        <div class="mg-menu-metrics">
            <div class="mg-menu-metric">
                <div class="mg-menu-metric__label">Calorias</div>
                <div class="mg-menu-metric__value"><span data-menu-total-kcal>0</span> kcal</div>
            </div>
            <div class="mg-menu-metric mg-menu-metric--protein">
                <div class="mg-menu-metric__label">Proteína</div>
                <div class="mg-menu-metric__value"><span data-menu-total-protein>0,0</span>g</div>
            </div>
            <div class="mg-menu-metric mg-menu-metric--carbs">
                <div class="mg-menu-metric__label">Carbo</div>
                <div class="mg-menu-metric__value"><span data-menu-total-carbs>0,0</span>g</div>
            </div>
            <div class="mg-menu-metric mg-menu-metric--fat">
                <div class="mg-menu-metric__label">Gordura</div>
                <div class="mg-menu-metric__value"><span data-menu-total-fat>0,0</span>g</div>
            </div>
        </div>

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
            <div>
                <div class="mg-panel-label mb-1">Refeições reais</div>
                <p class="mg-panel-hint mb-0">O total usa a fórmula (macro/100) × gramas, sem refresh.</p>
            </div>
            <button type="button" class="mg-btn-ghost" data-add-menu-meal @disabled($foods->isEmpty())>
                <i class="ri-add-line"></i> Adicionar refeição
            </button>
        </div>

        @if($foods->isEmpty())
            <div class="mg-empty-state py-3">
                <i class="ri-apple-line"></i>
                <p>Cadastre alimentos antes de montar cardápios reais.</p>
                <a href="{{ route('library.diet.foods') }}" class="mg-btn-primary">Cadastrar alimento</a>
            </div>
        @endif

        <div class="mg-menu-meals" data-menu-meals></div>
    </form>

    <div class="mg-menu-list">
        @forelse($menus as $menu)
            <div class="mg-menu-card">
                <div class="mg-menu-card__head">
                    <div class="min-w-0">
                        <div class="mg-menu-card__title">{{ $menu->name }}</div>
                        <div class="mg-menu-card__meta">
                            <span>{{ $menu->meals_count }} refeições</span>
                            <span>{{ number_format($menu->total_calories, 0, ',', '.') }} kcal</span>
                            @if($menu->description)
                                <span>{{ \Illuminate\Support\Str::limit($menu->description, 82) }}</span>
                            @endif
                        </div>
                    </div>
                    <span class="mg-chip {{ $menu->status === 'published' ? 'mg-chip--success' : '' }}">
                        {{ $menu->status === 'published' ? 'Publicado' : 'Rascunho' }}
                    </span>
                </div>

                @if($menu->meals->isNotEmpty())
                    <div class="mg-menu-card__meals">
                        @foreach($menu->meals as $meal)
                            @php $macros = $meal->computedMacros(); @endphp
                            <div class="mg-menu-card__meal">
                                <div class="min-w-0">
                                    <div class="mg-menu-card__meal-title">
                                        {{ $meal->name }}
                                        @if($meal->time_label)<span class="text-muted fw-semibold ms-1">{{ $meal->time_label }}</span>@endif
                                    </div>
                                    <div class="mg-menu-card__meal-foods">
                                        @foreach($meal->mealFoods as $mealFood)
                                            {{ $mealFood->dietFood?->name }} {{ (float) $mealFood->quantity_in_grams }}g{{ ! $loop->last ? ' · ' : '' }}
                                        @endforeach
                                    </div>
                                </div>
                                <span class="mg-menu-total-chip">
                                    {{ number_format($macros['calories'], 0, ',', '.') }} kcal ·
                                    P {{ number_format($macros['protein'], 1, ',', '.') }} ·
                                    C {{ number_format($macros['carbs'], 1, ',', '.') }} ·
                                    G {{ number_format($macros['fat'], 1, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="mg-empty-state">
                <i class="ri-restaurant-line"></i>
                <p>Nenhum cardápio criado ainda.</p>
            </div>
        @endforelse
    </div>

    @if($menus->hasPages())
        <div class="mg-pagination">{{ $menus->links() }}</div>
    @endif
</div>
@endsection

@push('scripts')
    <script>
        (function () {
            const form = document.querySelector('[data-menu-builder-form]');
            const mealsContainer = document.querySelector('[data-menu-meals]');
            const dietFoods = @json($dietFoodOptions);
            const initialMeals = @json(array_values(old('meals', [])));
            let nextMealIndex = 0;

            if (!form || !mealsContainer) {
                return;
            }

            function escapeHtml(value) {
                const element = document.createElement('textarea');
                element.textContent = value ?? '';
                return element.innerHTML;
            }

            function escapeAttribute(value) {
                return String(value ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('"', '&quot;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;');
            }

            function formatDecimal(value, digits = 1) {
                return Number(value || 0).toLocaleString('pt-BR', {
                    minimumFractionDigits: digits,
                    maximumFractionDigits: digits,
                });
            }

            function formatKcal(value) {
                return Number(value || 0).toLocaleString('pt-BR', {
                    maximumFractionDigits: 0,
                });
            }

            function findFood(foodId) {
                return dietFoods.find((food) => String(food.id) === String(foodId));
            }

            function foodOptions(selectedId = '') {
                if (!dietFoods.length) {
                    return '<option value="">Cadastre alimentos antes</option>';
                }

                return '<option value="">Selecione...</option>' + dietFoods.map((food) => {
                    const selected = String(food.id) === String(selectedId) ? 'selected' : '';
                    return `<option value="${food.id}" ${selected}>${escapeHtml(food.label)}</option>`;
                }).join('');
            }

            function foodTemplate(mealIndex, foodIndex, foodItem = {}) {
                return `
                    <div class="mg-menu-food-row" data-menu-food-row>
                        <select name="meals[${mealIndex}][foods][${foodIndex}][diet_food_id]" class="mg-field mg-field--sm" data-menu-food-select required>
                            ${foodOptions(foodItem.diet_food_id)}
                        </select>
                        <input name="meals[${mealIndex}][foods][${foodIndex}][quantity_in_grams]" type="number" min="1" step="1" class="mg-field mg-field--sm" value="${escapeAttribute(foodItem.quantity_in_grams ?? 100)}" data-menu-food-grams required>
                        <div class="mg-menu-row-macros" data-menu-row-macros>0 kcal · P 0,0g · C 0,0g · G 0,0g</div>
                        <input name="meals[${mealIndex}][foods][${foodIndex}][notes]" class="mg-field mg-field--sm" value="${escapeAttribute(foodItem.notes)}" placeholder="Notas">
                        <button type="button" class="mg-icon-btn" data-remove-menu-food title="Remover alimento">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                `;
            }

            function mealTemplate(mealIndex, meal = {}) {
                return `
                    <div class="mg-menu-meal" data-menu-meal data-meal-index="${mealIndex}">
                        <div class="mg-menu-meal__head">
                            <div>
                                <label class="mg-field-label">Refeição</label>
                                <input name="meals[${mealIndex}][name]" class="mg-field mg-field--sm" value="${escapeAttribute(meal.name)}" placeholder="Ex: Café da manhã" required>
                            </div>
                            <div>
                                <label class="mg-field-label">Horário</label>
                                <input name="meals[${mealIndex}][time_label]" class="mg-field mg-field--sm" value="${escapeAttribute(meal.time_label)}" placeholder="07:00">
                            </div>
                            <div>
                                <label class="mg-field-label">Notas</label>
                                <input name="meals[${mealIndex}][notes]" class="mg-field mg-field--sm" value="${escapeAttribute(meal.notes)}" placeholder="Pré/pós, substituições...">
                            </div>
                            <span class="mg-menu-total-chip" data-menu-meal-total>0 kcal · P 0,0g · C 0,0g · G 0,0g</span>
                            <button type="button" class="mg-icon-btn" data-remove-menu-meal title="Remover refeição">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                        <div class="mg-menu-foods" data-menu-foods data-next-food-index="0"></div>
                        <button type="button" class="mg-btn-ghost mt-2" data-add-menu-food>
                            <i class="ri-add-line"></i> Adicionar alimento
                        </button>
                    </div>
                `;
            }

            function addMeal(meal = {}) {
                const mealIndex = nextMealIndex;
                mealsContainer.insertAdjacentHTML('beforeend', mealTemplate(mealIndex, meal));
                nextMealIndex += 1;

                const mealElement = mealsContainer.querySelector(`[data-meal-index="${mealIndex}"]`);
                const foods = Array.isArray(meal.foods) && meal.foods.length ? meal.foods : [{}];
                foods.forEach((foodItem) => addFood(mealElement, foodItem));
                syncMacros();
            }

            function addFood(mealElement, foodItem = {}) {
                const foodsContainer = mealElement.querySelector('[data-menu-foods]');
                const mealIndex = mealElement.dataset.mealIndex;
                const foodIndex = Number(foodsContainer.dataset.nextFoodIndex || 0);

                foodsContainer.insertAdjacentHTML('beforeend', foodTemplate(mealIndex, foodIndex, foodItem));
                foodsContainer.dataset.nextFoodIndex = foodIndex + 1;
                syncMacros();
            }

            function calculateFoodMacros(row) {
                const selectedFood = findFood(row.querySelector('[data-menu-food-select]')?.value);
                const grams = Number(row.querySelector('[data-menu-food-grams]')?.value || 0);
                const factor = grams / 100;

                return {
                    calories: (selectedFood?.calories || 0) * factor,
                    protein: (selectedFood?.protein || 0) * factor,
                    carbs: (selectedFood?.carbs || 0) * factor,
                    fat: (selectedFood?.fat || 0) * factor,
                };
            }

            function sumMacros(total, macros) {
                total.calories += macros.calories;
                total.protein += macros.protein;
                total.carbs += macros.carbs;
                total.fat += macros.fat;

                return total;
            }

            function macroText(macros) {
                return `${formatKcal(macros.calories)} kcal · P ${formatDecimal(macros.protein)}g · C ${formatDecimal(macros.carbs)}g · G ${formatDecimal(macros.fat)}g`;
            }

            function syncMacros() {
                const totals = { calories: 0, protein: 0, carbs: 0, fat: 0 };
                const meals = Array.from(mealsContainer.querySelectorAll('[data-menu-meal]'));

                meals.forEach((mealElement) => {
                    const mealTotals = { calories: 0, protein: 0, carbs: 0, fat: 0 };

                    mealElement.querySelectorAll('[data-menu-food-row]').forEach((foodRow) => {
                        const foodMacros = calculateFoodMacros(foodRow);
                        sumMacros(mealTotals, foodMacros);
                        foodRow.querySelector('[data-menu-row-macros]').textContent = macroText(foodMacros);
                    });

                    sumMacros(totals, mealTotals);
                    mealElement.querySelector('[data-menu-meal-total]').textContent = macroText(mealTotals);
                });

                form.querySelector('[data-menu-total-kcal]').textContent = formatKcal(totals.calories);
                form.querySelector('[data-menu-total-protein]').textContent = formatDecimal(totals.protein);
                form.querySelector('[data-menu-total-carbs]').textContent = formatDecimal(totals.carbs);
                form.querySelector('[data-menu-total-fat]').textContent = formatDecimal(totals.fat);
                form.querySelector('[data-menu-meals-count]').textContent = meals.length;
                form.querySelector('[data-menu-meals-count-input]').value = meals.length;
                form.querySelector('[data-menu-total-calories-input]').value = Math.round(totals.calories);
            }

            form.addEventListener('click', function (event) {
                const addMealButton = event.target.closest('[data-add-menu-meal]');
                const addFoodButton = event.target.closest('[data-add-menu-food]');
                const removeFoodButton = event.target.closest('[data-remove-menu-food]');
                const removeMealButton = event.target.closest('[data-remove-menu-meal]');

                if (addMealButton) {
                    addMeal();
                }

                if (addFoodButton) {
                    addFood(addFoodButton.closest('[data-menu-meal]'));
                }

                if (removeFoodButton) {
                    removeFoodButton.closest('[data-menu-food-row]')?.remove();
                    syncMacros();
                }

                if (removeMealButton) {
                    removeMealButton.closest('[data-menu-meal]')?.remove();
                    syncMacros();
                }
            });

            form.addEventListener('input', syncMacros);
            form.addEventListener('change', syncMacros);

            if (dietFoods.length) {
                if (initialMeals.length) {
                    initialMeals.forEach((meal) => addMeal(meal));
                } else {
                    addMeal();
                }
            }

            syncMacros();
        })();
    </script>
@endpush
