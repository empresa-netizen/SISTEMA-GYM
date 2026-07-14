@php
    $deliveryBadge = ['PENDING' => 'mg-chip--warn', 'DELIVERED' => 'mg-chip--success', 'LATE' => 'mg-chip--danger'];
    $deliveryLabels = ['PENDING' => 'Pendente', 'DELIVERED' => 'Entregue', 'LATE' => 'Atrasada'];
    $dietLogs = $member->logbooks->filter(fn ($entry) => strtoupper((string) $entry->type) === 'DIET');
    $dietSort = request('diet_sort', 'recent');
    $dietPrescriptions = $dietSort === 'oldest'
        ? $member->dietPrescriptions->sortBy('created_at')
        : $member->dietPrescriptions->sortByDesc('created_at');
    $dietFoodCatalog = collect($dietFoods ?? [])->values();
    $dietMenuOptions = collect($dietMenus ?? [])->values();
    $dietFoodOptions = $dietFoodCatalog->map(fn ($food) => [
        'id' => $food->id,
        'name' => $food->name,
        'label' => trim($food->name.' · '.number_format((float) $food->calories, 0, ',', '.').' kcal/100g'),
        'group' => $food->food_group,
        'calories' => (float) $food->calories,
        'protein' => (float) $food->protein,
        'carbs' => (float) $food->carbs,
        'fat' => (float) $food->fat,
    ])->values();
@endphp

@push('styles')
<style>
    .mg-rx--diet {
        --diet-bg: #020817;
        --diet-panel: #111A2B;
        --diet-panel-soft: #151F32;
        --diet-line: rgba(87, 111, 148, 0.28);
        --diet-blue: #3B95FF;
        --diet-yellow: #F6B23D;
        --diet-red: #F46F68;
        --diet-text: #F6F8FC;
        --diet-muted: #9EAAC0;
    }

    .mg-rx--diet .mg-tab-block__head {
        align-items: center;
        margin-bottom: 1.08rem;
        padding-bottom: 0.85rem;
        border-bottom: 1px solid rgba(72, 94, 126, 0.34);
    }

    .mg-rx--diet .mg-tab-block__title {
        margin: 0;
        color: var(--diet-text);
        font-size: 1.18rem;
        font-weight: 900;
        letter-spacing: -0.025em;
    }

    .mg-rx--diet .mg-rx-subnav {
        height: 4.05rem;
        margin-bottom: 1rem;
        padding: 0.45rem;
        border: 1px solid var(--diet-line);
        border-radius: 0.95rem;
        background: #080E1A !important;
    }

    .mg-rx--diet .mg-tab-subnav__item {
        min-height: 2.86rem;
        padding: 0 1rem;
        border-radius: 0.7rem;
        color: var(--diet-muted) !important;
        font-size: 0.88rem;
        font-weight: 860;
    }

    .mg-rx--diet .mg-tab-subnav__item.is-active {
        background: #202C3E !important;
        color: #EAF2FF !important;
    }

    .mg-rx--diet .mg-prescription-list {
        gap: 0.7rem;
    }

    .mg-rx--diet .mg-prescription-card {
        min-height: 4.9rem;
        padding: 0.82rem 0.95rem;
        border-radius: 0.9rem;
        background: var(--diet-panel) !important;
    }

    .mg-rx--diet .mg-rx-card__icon {
        width: 2.2rem;
        height: 2.2rem;
        border: 0;
        border-radius: 999px;
        background: rgba(59, 149, 255, 0.14) !important;
        color: #8CC5FF !important;
    }

    .mg-rx--diet .mg-rx-macro-bars {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.45rem;
        max-width: 35rem;
        margin-top: 0.58rem;
    }

    .mg-rx--diet .mg-rx-macro-line {
        display: grid;
        grid-template-columns: auto minmax(5.2rem, 1fr) auto;
        align-items: center;
        gap: 0.45rem;
        padding: 0;
        border: 0 !important;
        background: transparent !important;
        color: var(--diet-muted) !important;
        font-size: 0.72rem;
        font-weight: 850;
    }

    .mg-rx--diet .mg-rx-macro-line i {
        height: 0.28rem;
        border-radius: 999px;
        background: rgba(103, 128, 163, 0.32) !important;
        overflow: hidden;
    }

    .mg-rx--diet .mg-rx-macro-line i::after {
        width: var(--macro-fill);
        border-radius: inherit;
    }

    .mg-rx--diet .mg-rx-macro-line--protein i::after {
        background: var(--diet-blue);
    }

    .mg-rx--diet .mg-rx-macro-line--carbs i::after {
        background: var(--diet-yellow);
    }

    .mg-rx--diet .mg-rx-macro-line--fat i::after {
        background: #C6CDD8;
    }

    .mg-rx--diet .mg-rx-meal-row,
    .mg-rx--diet .mg-rx-view-meal {
        border: 1px solid rgba(71, 93, 124, 0.35) !important;
        border-radius: 0.7rem;
        background: #0B1424 !important;
    }

    .mg-rx-modal--wide .modal-dialog,
    .mg-rx-modal--wide {
        max-width: min(1120px, calc(100vw - 2rem));
    }

    .mg-rx-modal .modal-content {
        border: 1px solid rgba(87, 111, 148, 0.36) !important;
        border-radius: 1rem;
        background: #111A2B !important;
    }

    .mg-rx-modal .modal-header,
    .mg-rx-modal .modal-footer {
        border-color: rgba(87, 111, 148, 0.28) !important;
    }

    .mg-rx-modal .modal-body {
        background: #0B1220 !important;
    }

    .mg-rx--diet .mg-rx-form-grid,
    .mg-rx-modal .mg-rx-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.68rem;
    }

    .mg-rx-form-span {
        grid-column: 1 / -1;
    }

    .mg-rx-builder {
        margin-top: 0.9rem;
        border-radius: 0.95rem;
        background: #111A2B !important;
    }

    .mg-rx-builder__head,
    .mg-rx-meal-builder__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding-bottom: 0.55rem;
        border-bottom: 1px solid rgba(87, 111, 148, 0.24);
    }

    .mg-rx-meal-total {
        display: inline-flex;
        align-items: center;
        min-height: 1.55rem;
        padding: 0 0.55rem;
        border-radius: 999px;
        background: rgba(59, 149, 255, 0.14);
        color: #BBD7FF;
        font-size: 0.72rem;
        font-weight: 900;
    }

    .mg-rx-macro-strip {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.55rem;
        margin: 0.75rem 0;
    }

    .mg-rx-macro-pill {
        min-height: 4.6rem;
        border: 1px solid rgba(87, 111, 148, 0.28) !important;
        border-radius: 0.84rem;
        background: #0B1424 !important;
    }

    .mg-rx-food-row {
        grid-template-columns: minmax(14rem, 1.3fr) 5.4rem minmax(13rem, 0.9fr) minmax(10rem, 0.8fr) 2.8rem;
        gap: 0.45rem;
        padding: 0.5rem;
        border-radius: 0.78rem;
        background: #0B1424 !important;
    }

    .mg-rx-food-macros {
        min-height: 2.2rem;
        display: flex;
        align-items: center;
        color: #C8D6EA !important;
        font-size: 0.75rem;
        font-weight: 850;
        white-space: nowrap;
    }

    @media (max-width: 980px) {
        .mg-rx--diet .mg-rx-form-grid,
        .mg-rx-modal .mg-rx-form-grid,
        .mg-rx-food-row,
        .mg-rx-macro-strip {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

<div class="mg-tab-block mg-rx mg-rx--diet">
    <div class="mg-tab-block__head">
        <div>
            <p class="mg-section-label mb-1">Nutrição</p>
            <h2 class="mg-tab-block__title">Dieta</h2>
        </div>
        <div class="mg-tab-actions mg-rx-actions">
            <button type="button" class="mg-btn-primary mg-rx-btn" data-bs-toggle="modal" data-bs-target="#dietModal">
                <i class="ri-add-line"></i> Novo plano alimentar
            </button>
            <button type="button" class="mg-btn-ghost mg-rx-btn" data-bs-toggle="modal" data-bs-target="#notifyClientModal">
                <i class="ri-notification-3-line"></i> Notificar cliente
            </button>
        </div>
    </div>

    <div class="mg-tab-subnav mg-rx-subnav">
        <a href="#diet-prescriptions" class="mg-tab-subnav__item is-active">Prescrições <span>{{ $dietPrescriptions->count() }}</span></a>
        <a href="#diet-records" class="mg-tab-subnav__item">Registros <span>{{ $dietLogs->count() }}</span></a>
        <form method="GET" action="{{ route('members.show', [$member, 'tab' => 'diet']) }}" class="mg-tab-sort mg-rx-sort">
            <input type="hidden" name="tab" value="diet">
            <label class="mg-field-label mb-0">Ordenar</label>
            <select name="diet_sort" class="mg-field mg-field--sm" onchange="this.form.submit()">
                <option value="recent" @selected($dietSort === 'recent')>Mais recentes</option>
                <option value="oldest" @selected($dietSort === 'oldest')>Mais antigos</option>
            </select>
        </form>
    </div>

    <section id="diet-prescriptions" class="mg-tab-section">
        <div class="mg-prescription-list">
        @forelse($dietPrescriptions as $diet)
            @php
                $menu = $diet->dietMenu;
                $menuMacros = $menu?->computedMacros();
                $macroTotal = $menuMacros ? max(1, (float) $menuMacros['protein'] + (float) $menuMacros['carbs'] + (float) $menuMacros['fat']) : 1;
                $proteinPercent = $menuMacros ? min(100, ((float) $menuMacros['protein'] / $macroTotal) * 100) : 0;
                $carbsPercent = $menuMacros ? min(100, ((float) $menuMacros['carbs'] / $macroTotal) * 100) : 0;
                $fatPercent = $menuMacros ? min(100, ((float) $menuMacros['fat'] / $macroTotal) * 100) : 0;
            @endphp
            <article class="mg-prescription-card mg-rx-card">
                <div class="mg-prescription-card__main">
                    <span class="mg-rx-card__icon"><i class="ri-restaurant-line"></i></span>
                    <div class="mg-rx-card__content">
                        <div class="mg-prescription-card__eyebrow">{{ $diet->scheduled_at?->format('d/m/Y H:i') ?? $diet->created_at?->format('d/m/Y') }}</div>
                        <h3 class="mg-prescription-card__title">{{ $diet->title }}</h3>
                        <div class="mg-prescription-card__meta">
                            <span>{{ $menu?->name ?? 'Plano personalizado' }}</span>
                            @if($menuMacros)
                                <span>{{ $menuMacros['meals_count'] }} refeições</span>
                                <span>{{ number_format((float) $menuMacros['calories'], 0, ',', '.') }} kcal</span>
                                <span>P {{ number_format((float) $menuMacros['protein'], 1, ',', '.') }}g</span>
                                <span>C {{ number_format((float) $menuMacros['carbs'], 1, ',', '.') }}g</span>
                                <span>G {{ number_format((float) $menuMacros['fat'], 1, ',', '.') }}g</span>
                            @endif
                        </div>
                        @if($menu?->meals->isNotEmpty())
                            @if($menuMacros)
                                <div class="mg-rx-macro-bars" aria-label="Distribuição de macros">
                                    <span class="mg-rx-macro-line mg-rx-macro-line--protein" style="--macro-fill: {{ $proteinPercent }}%">
                                        <b>Proteína</b><i></i><em>{{ number_format((float) $menuMacros['protein'], 1, ',', '.') }}g</em>
                                    </span>
                                    <span class="mg-rx-macro-line mg-rx-macro-line--carbs" style="--macro-fill: {{ $carbsPercent }}%">
                                        <b>Carbo</b><i></i><em>{{ number_format((float) $menuMacros['carbs'], 1, ',', '.') }}g</em>
                                    </span>
                                    <span class="mg-rx-macro-line mg-rx-macro-line--fat" style="--macro-fill: {{ $fatPercent }}%">
                                        <b>Gordura</b><i></i><em>{{ number_format((float) $menuMacros['fat'], 1, ',', '.') }}g</em>
                                    </span>
                                </div>
                            @endif
                            <div class="mg-rx-meal-preview">
                                @foreach($menu->meals->take(3) as $meal)
                                    @php $mealMacros = $meal->computedMacros(); @endphp
                                    <div class="mg-rx-meal-row">
                                        <span>
                                            <strong>{{ $meal->name }}</strong>
                                            @if($meal->time_label)<small>{{ $meal->time_label }}</small>@endif
                                            <em>
                                                @foreach($meal->mealFoods as $mealFood)
                                                    {{ $mealFood->dietFood?->name }} {{ (float) $mealFood->quantity_in_grams }}g{{ ! $loop->last ? ' · ' : '' }}
                                                @endforeach
                                            </em>
                                        </span>
                                        <span class="mg-rx-mini-chip mg-rx-mini-chip--blue">{{ (int) $mealMacros['calories'] }} kcal</span>
                                    </div>
                                @endforeach
                                @if($menu->meals->count() > 3)
                                    <span class="mg-rx-more">+{{ $menu->meals->count() - 3 }} refeições no plano.</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
                <div class="mg-card-actions">
                    <span class="mg-chip mg-rx-status {{ $deliveryBadge[$diet->delivery_status] ?? '' }}">{{ $deliveryLabels[$diet->delivery_status] ?? $diet->delivery_status }}</span>
                    <button type="button" class="mg-icon-btn" title="Visualizar" data-bs-toggle="modal" data-bs-target="#dietViewModal{{ $diet->id }}"><i class="ri-eye-line"></i></button>
                    <a href="{{ route('diet-prescriptions.print', $diet) }}" class="mg-icon-btn" title="Gerar PDF / Imprimir" target="_blank" rel="noopener"><i class="ri-printer-line"></i></a>
                    <button type="button" class="mg-icon-btn" title="Editar" data-bs-toggle="modal" data-bs-target="#dietEditModal{{ $diet->id }}"><i class="ri-pencil-line"></i></button>
                    <div class="dropdown">
                        <button class="mg-icon-btn" data-bs-toggle="dropdown" type="button"><i class="ri-more-2-fill"></i></button>
                        <div class="dropdown-menu dropdown-menu-end mg-dropdown">
                            @if(($diet->status ?? null) !== 'sent')
                                <form method="POST" action="{{ route('diet-prescriptions.send', $diet) }}">
                                    @csrf
                                    <button class="dropdown-item">Enviar ao cliente</button>
                                </form>
                            @else
                                <span class="dropdown-item-text">Enviada {{ $diet->sent_at?->format('d/m') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </article>

            <div class="modal fade" id="dietViewModal{{ $diet->id }}" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-centered mg-rx-modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ $diet->title }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mg-rx-chip-row mb-3">
                                <span class="mg-rx-mini-chip {{ $deliveryBadge[$diet->delivery_status] ?? '' }}">{{ $deliveryLabels[$diet->delivery_status] ?? $diet->delivery_status }}</span>
                                <span class="mg-rx-mini-chip">{{ $menu?->name ?? 'Plano personalizado' }}</span>
                                @if($menuMacros)
                                    <span class="mg-rx-mini-chip mg-rx-mini-chip--blue">{{ number_format((float) $menuMacros['calories'], 0, ',', '.') }} kcal</span>
                                    <span class="mg-rx-mini-chip">P {{ number_format((float) $menuMacros['protein'], 1, ',', '.') }}g</span>
                                    <span class="mg-rx-mini-chip">C {{ number_format((float) $menuMacros['carbs'], 1, ',', '.') }}g</span>
                                    <span class="mg-rx-mini-chip">G {{ number_format((float) $menuMacros['fat'], 1, ',', '.') }}g</span>
                                @endif
                            </div>
                            @if($diet->notes)
                                <p class="mb-3"><strong>Observações:</strong> {{ $diet->notes }}</p>
                            @endif
                            @if($menu?->meals->isNotEmpty())
                                <div class="d-grid gap-3">
                                    @foreach($menu->meals as $meal)
                                        @php $mealMacros = $meal->computedMacros(); @endphp
                                        <div class="mg-rx-view-meal">
                                            <div class="d-flex justify-content-between gap-2">
                                                <strong>{{ $meal->name }} @if($meal->time_label)<span class="text-muted">· {{ $meal->time_label }}</span>@endif</strong>
                                                <span class="mg-rx-mini-chip mg-rx-mini-chip--blue">{{ (int) $mealMacros['calories'] }} kcal</span>
                                            </div>
                                            <div class="table-responsive mt-2">
                                                <table class="table table-sm mb-0 mg-rx-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Alimento</th>
                                                            <th class="text-end">g</th>
                                                            <th class="text-end">Kcal</th>
                                                            <th class="text-end">P</th>
                                                            <th class="text-end">C</th>
                                                            <th class="text-end">G</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($meal->mealFoods as $mealFood)
                                                            @php $portion = $mealFood->portionMacros(); @endphp
                                                            <tr>
                                                                <td>{{ $mealFood->dietFood?->name }}</td>
                                                                <td class="text-end">{{ number_format((float) $mealFood->quantity_in_grams, 0, ',', '.') }}</td>
                                                                <td class="text-end">{{ number_format((float) $portion['calories'], 0, ',', '.') }}</td>
                                                                <td class="text-end">{{ number_format((float) $portion['protein'], 1, ',', '.') }}</td>
                                                                <td class="text-end">{{ number_format((float) $portion['carbs'], 1, ',', '.') }}</td>
                                                                <td class="text-end">{{ number_format((float) $portion['fat'], 1, ',', '.') }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted mb-0">Esta prescrição ainda não possui refeições detalhadas.</p>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <a href="{{ route('diet-prescriptions.print', $diet) }}" class="mg-btn-primary mg-rx-btn" target="_blank" rel="noopener">
                                <i class="ri-printer-line me-1"></i> Gerar PDF / Imprimir
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="dietEditModal{{ $diet->id }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered mg-rx-modal">
                    <form method="POST" action="{{ route('diet-prescriptions.update', $diet) }}" class="modal-content">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Editar plano alimentar</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="mg-field-label">Título</label>
                                <input type="text" name="title" class="mg-field mg-rx-input" value="{{ $diet->title }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="mg-field-label">Cardápio base</label>
                                <select name="diet_menu_id" class="mg-field mg-rx-input">
                                    <option value="">Personalizado sem cardápio</option>
                                    @foreach($dietMenuOptions as $menuOption)
                                        <option value="{{ $menuOption->id }}" @selected((int) $diet->diet_menu_id === (int) $menuOption->id)>{{ $menuOption->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="mg-field-label">Agendar para</label>
                                <input type="datetime-local" name="scheduled_at" class="mg-field mg-rx-input" value="{{ optional($diet->scheduled_at)->format('Y-m-d\TH:i') }}">
                            </div>
                            <div class="mb-3">
                                <label class="mg-field-label">Observações</label>
                                <textarea name="notes" class="mg-field mg-rx-input" rows="2">{{ $diet->notes }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="mg-btn-ghost mg-rx-btn" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="mg-btn-primary mg-rx-btn">Salvar alterações</button>
                        </div>
                    </form>
                </div>
            </div>
        @empty
            <div class="mg-empty-state mg-empty-state--compact mg-rx-empty">
                <i class="ri-restaurant-line"></i>
                <p>Nenhum plano alimentar cadastrado.</p>
                <button type="button" class="mg-btn-primary mg-rx-btn" data-bs-toggle="modal" data-bs-target="#dietModal">Nova prescrição</button>
            </div>
        @endforelse
        </div>
    </section>

    <section id="diet-records" class="mg-tab-section">
        <div class="mg-tab-section__head">
            <h3>Registros alimentares</h3>
        </div>
        <div class="mg-prescription-list">
        @forelse($dietLogs as $entry)
            <article class="mg-prescription-card mg-rx-card">
                <div class="mg-prescription-card__main">
                    <span class="mg-rx-card__icon"><i class="ri-book-open-line"></i></span>
                    <div class="mg-rx-card__content">
                        <div class="mg-prescription-card__eyebrow">{{ $entry->logged_at?->format('d/m/Y') }}</div>
                        <h3 class="mg-prescription-card__title">{{ $entry->title }}</h3>
                        <div class="mg-prescription-card__meta">
                            @if($entry->rating)<span>{{ $entry->rating }}/5</span>@endif
                            @if($entry->comment)<span>{{ $entry->comment }}</span>@endif
                        </div>
                    </div>
                </div>
                <span class="mg-chip mg-rx-status mg-chip--info">Registro</span>
            </article>
        @empty
            <div class="mg-empty-state mg-empty-state--compact mg-rx-empty">
                <i class="ri-book-open-line"></i>
                <p>Nenhum registro alimentar encontrado.</p>
            </div>
        @endforelse
        </div>
    </section>
</div>

<div class="modal fade" id="dietModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered mg-rx-modal mg-rx-modal--wide">
        <form method="POST" action="{{ route('members.diet.store', $member) }}" class="modal-content" data-diet-builder>
            @csrf
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">Nova prescrição alimentar</h5>
                    <p class="text-muted small mb-0">Use um cardápio pronto ou monte refeições com alimentos do catálogo.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mg-rx-form-grid">
                    <div>
                        <label class="mg-field-label">Título da prescrição</label>
                        <input type="text" name="title" class="mg-field mg-rx-input" placeholder="Ex: Cutting semana 2" required>
                    </div>
                    <div>
                        <label class="mg-field-label">Cardápio base</label>
                        <select name="diet_menu_id" class="mg-field mg-rx-input">
                            <option value="">Sem cardápio base</option>
                            @foreach($dietMenuOptions as $menuOption)
                                @php $macros = $menuOption->computedMacros(); @endphp
                                <option value="{{ $menuOption->id }}">
                                    {{ $menuOption->name }} · {{ $macros['meals_count'] ?: $menuOption->meals_count }} refeições · {{ number_format((float) ($macros['calories'] ?: $menuOption->total_calories), 0, ',', '.') }} kcal
                                </option>
                            @endforeach
                        </select>
                        <div class="mg-rx-help">Adicionar refeições abaixo cria um cardápio personalizado.</div>
                    </div>
                    <div>
                        <label class="mg-field-label">Nome do cardápio personalizado</label>
                        <input type="text" name="menu_name" class="mg-field mg-rx-input" placeholder="Ex: Dieta {{ $member->name }} — semana 1">
                    </div>
                    <div>
                        <label class="mg-field-label">Agendar para</label>
                        <input type="datetime-local" name="scheduled_at" class="mg-field mg-rx-input" value="{{ now()->format('Y-m-d\TH:i') }}">
                    </div>
                    <div class="mg-rx-form-span">
                        <label class="mg-field-label">Observações ao aluno</label>
                        <textarea name="notes" class="mg-field mg-rx-input" rows="2" placeholder="Ex: beber 2,5L de água, ajustar sal conforme orientação..."></textarea>
                    </div>
                </div>

                <div class="mg-rx-builder">
                    <div class="mg-rx-builder__head">
                        <div>
                            <div class="mg-rx-builder__title">Refeições e alimentos</div>
                            <p class="mg-rx-builder__hint">Filtre o catálogo, ajuste gramas e veja kcal/proteína/carbo/gordura em tempo real.</p>
                        </div>
                        <button type="button" class="mg-btn-ghost mg-rx-btn" data-add-diet-meal @disabled($dietFoodCatalog->isEmpty())>
                            <i class="ri-add-line"></i> Adicionar refeição
                        </button>
                    </div>

                    @if($dietFoodCatalog->isEmpty())
                        <div class="mg-rx-alert mg-rx-alert--warn">
                            Cadastre alimentos no catálogo antes de montar refeições detalhadas.
                            <a href="{{ route('library.diet.foods') }}" class="alert-link">Abrir catálogo</a>
                        </div>
                    @else
                        <div class="mg-rx-macro-strip" data-diet-macro-summary>
                            <span class="mg-rx-macro-pill mg-rx-macro-pill--calories">
                                <small>Kcal</small>
                                <strong data-total-calories>0</strong>
                            </span>
                            <span class="mg-rx-macro-pill mg-rx-macro-pill--protein" data-macro-bar="protein" style="--macro-fill: 0%">
                                <small>Proteína</small>
                                <strong data-total-protein>0g</strong>
                                <i></i>
                            </span>
                            <span class="mg-rx-macro-pill mg-rx-macro-pill--carbs" data-macro-bar="carbs" style="--macro-fill: 0%">
                                <small>Carbo</small>
                                <strong data-total-carbs>0g</strong>
                                <i></i>
                            </span>
                            <span class="mg-rx-macro-pill mg-rx-macro-pill--fat" data-macro-bar="fat" style="--macro-fill: 0%">
                                <small>Gordura</small>
                                <strong data-total-fat>0g</strong>
                                <i></i>
                            </span>
                        </div>
                        <div class="mg-rx-meals-container" data-diet-meals-container></div>
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="mg-btn-ghost mg-rx-btn" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="mg-btn-primary mg-rx-btn"><i class="ri-save-line me-1"></i> Salvar prescrição</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const builder = document.querySelector('[data-diet-builder]');
    const foods = @json($dietFoodOptions).map((food) => ({
        ...food,
        calories: Number(food.calories) || 0,
        protein: Number(food.protein) || 0,
        carbs: Number(food.carbs) || 0,
        fat: Number(food.fat) || 0,
    }));
    const mealsContainer = builder?.querySelector('[data-diet-meals-container]');
    const totalNodes = {
        calories: builder?.querySelector('[data-total-calories]'),
        protein: builder?.querySelector('[data-total-protein]'),
        carbs: builder?.querySelector('[data-total-carbs]'),
        fat: builder?.querySelector('[data-total-fat]'),
    };
    const macroBars = {
        protein: builder?.querySelector('[data-macro-bar="protein"]'),
        carbs: builder?.querySelector('[data-macro-bar="carbs"]'),
        fat: builder?.querySelector('[data-macro-bar="fat"]'),
    };

    if (!builder || !foods.length || !mealsContainer) {
        return;
    }

    let mealIndex = 0;

    const escapeHtml = (value) => {
        const element = document.createElement('div');
        element.textContent = value ?? '';
        return element.innerHTML;
    };

    const numberFormat = (value, decimals = 0) => Number(value || 0).toLocaleString('pt-BR', {
        maximumFractionDigits: decimals,
        minimumFractionDigits: decimals,
    });

    const parseGrams = (value) => {
        const parsed = Number.parseFloat(String(value || '').replace(',', '.'));

        return Number.isFinite(parsed) ? Math.max(parsed, 0) : 0;
    };

    const syncMacroBars = (totals) => {
        const totalMacroGrams = Math.max(1, totals.protein + totals.carbs + totals.fat);

        Object.entries(macroBars).forEach(([key, node]) => {
            if (!node) {
                return;
            }

            node.style.setProperty('--macro-fill', `${Math.min(100, (totals[key] / totalMacroGrams) * 100)}%`);
        });
    };

    const foodOptions = (query = '') => {
        const normalizedQuery = query.trim().toLocaleLowerCase('pt-BR');
        const visibleFoods = normalizedQuery
            ? foods.filter((food) => `${food.name} ${food.group || ''}`.toLocaleLowerCase('pt-BR').includes(normalizedQuery))
            : foods;

        return '<option value="">Selecione...</option>' + visibleFoods
            .map((food) => `<option value="${food.id}">${escapeHtml(food.label)}</option>`)
            .join('');
    };

    const refreshFoodOptions = (select, query = '') => {
        const currentValue = select.value;
        select.innerHTML = foodOptions(query);

        if (currentValue && [...select.options].some((option) => option.value === currentValue)) {
            select.value = currentValue;
            return;
        }

        select.value = '';
    };

    const findFood = (id) => foods.find((food) => String(food.id) === String(id));

    const foodRowTemplate = (currentMealIndex, currentFoodIndex) => `
        <div class="mg-rx-food-row" data-diet-food-row>
            <div class="mg-rx-food-cell mg-rx-food-cell--food">
                <label class="mg-field-label">Alimento</label>
                <div class="mg-rx-food-picker">
                    <input type="search" class="mg-field mg-rx-input mg-rx-quiet-field" placeholder="Filtrar..." data-diet-food-search>
                    <select name="meals[${currentMealIndex}][foods][${currentFoodIndex}][diet_food_id]" class="mg-field mg-rx-input mg-rx-quiet-field" data-diet-food-select required>
                        ${foodOptions()}
                    </select>
                </div>
            </div>
            <div class="mg-rx-food-cell">
                <label class="mg-field-label">g</label>
                <input name="meals[${currentMealIndex}][foods][${currentFoodIndex}][quantity_in_grams]" type="number" min="1" step="1" class="mg-field mg-rx-input mg-rx-quiet-field" value="100" data-diet-food-grams required>
            </div>
            <div class="mg-rx-food-cell mg-rx-food-cell--macros">
                <label class="mg-field-label">Macros</label>
                <div class="mg-rx-food-macros" data-diet-food-macros>0 kcal</div>
            </div>
            <div class="mg-rx-food-cell">
                <label class="mg-field-label">Notas</label>
                <input name="meals[${currentMealIndex}][foods][${currentFoodIndex}][notes]" class="mg-field mg-rx-input mg-rx-quiet-field" placeholder="opcional">
            </div>
            <div class="mg-rx-food-cell mg-rx-food-cell--action">
                <button type="button" class="mg-icon-btn" data-remove-diet-food title="Remover alimento">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
        </div>
    `;

    const macroSummary = (totals) => `${numberFormat(totals.calories)} kcal · P ${numberFormat(totals.protein, 1)}g · C ${numberFormat(totals.carbs, 1)}g · G ${numberFormat(totals.fat, 1)}g`;

    const syncMacros = () => {
        if (!totalNodes.calories || !totalNodes.protein || !totalNodes.carbs || !totalNodes.fat) {
            return;
        }

        const totals = { calories: 0, protein: 0, carbs: 0, fat: 0 };

        builder.querySelectorAll('[data-diet-meal]').forEach((meal) => {
            const mealTotals = { calories: 0, protein: 0, carbs: 0, fat: 0 };

            meal.querySelectorAll('[data-diet-food-row]').forEach((row) => {
                const food = findFood(row.querySelector('[data-diet-food-select]')?.value);
                const grams = parseGrams(row.querySelector('[data-diet-food-grams]')?.value);
                const factor = grams / 100;
                const rowTotals = {
                    calories: food ? food.calories * factor : 0,
                    protein: food ? food.protein * factor : 0,
                    carbs: food ? food.carbs * factor : 0,
                    fat: food ? food.fat * factor : 0,
                };

                mealTotals.calories += rowTotals.calories;
                mealTotals.protein += rowTotals.protein;
                mealTotals.carbs += rowTotals.carbs;
                mealTotals.fat += rowTotals.fat;

                totals.calories += rowTotals.calories;
                totals.protein += rowTotals.protein;
                totals.carbs += rowTotals.carbs;
                totals.fat += rowTotals.fat;

                const macroNode = row.querySelector('[data-diet-food-macros]');
                if (macroNode) {
                    macroNode.textContent = macroSummary(rowTotals);
                }
            });

            const mealTotalNode = meal.querySelector('[data-diet-meal-total]');
            if (mealTotalNode) {
                mealTotalNode.textContent = macroSummary(mealTotals);
            }
        });

        totalNodes.calories.textContent = `${numberFormat(totals.calories)} kcal`;
        totalNodes.protein.textContent = `${numberFormat(totals.protein, 1)}g`;
        totalNodes.carbs.textContent = `${numberFormat(totals.carbs, 1)}g`;
        totalNodes.fat.textContent = `${numberFormat(totals.fat, 1)}g`;
        syncMacroBars(totals);
    };

    const addFood = (meal) => {
        if (!meal) {
            return;
        }

        const foodsContainer = meal.querySelector('[data-diet-foods]');
        if (!foodsContainer) {
            return;
        }

        const currentMealIndex = meal.dataset.mealIndex;
        const currentFoodIndex = Number(foodsContainer.dataset.nextFoodIndex || 0);

        foodsContainer.insertAdjacentHTML('beforeend', foodRowTemplate(currentMealIndex, currentFoodIndex));
        foodsContainer.dataset.nextFoodIndex = currentFoodIndex + 1;
        syncMacros();
    };

    const addMeal = () => {
        const currentMealIndex = mealIndex;

        mealsContainer.insertAdjacentHTML('beforeend', `
            <div class="mg-rx-meal-builder" data-diet-meal data-meal-index="${currentMealIndex}">
                <div class="mg-rx-meal-builder__head">
                    <strong>Refeição ${currentMealIndex + 1}</strong>
                    <div class="d-flex align-items-center gap-2">
                        <span class="mg-rx-meal-total" data-diet-meal-total>0 kcal · P 0,0g · C 0,0g · G 0,0g</span>
                        <button type="button" class="mg-icon-btn" data-remove-diet-meal title="Remover refeição">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                </div>
                <div class="mg-rx-meal-grid">
                    <div>
                        <label class="mg-field-label">Nome</label>
                        <input name="meals[${currentMealIndex}][name]" class="mg-field mg-rx-input mg-rx-quiet-field" placeholder="Ex: Café da manhã" required>
                    </div>
                    <div>
                        <label class="mg-field-label">Horário</label>
                        <input name="meals[${currentMealIndex}][time_label]" class="mg-field mg-rx-input mg-rx-quiet-field" placeholder="07:00">
                    </div>
                    <div>
                        <label class="mg-field-label">Notas</label>
                        <input name="meals[${currentMealIndex}][notes]" class="mg-field mg-rx-input mg-rx-quiet-field" placeholder="opcional">
                    </div>
                </div>
                <div class="mg-rx-foods" data-diet-foods data-next-food-index="0"></div>
                <button type="button" class="mg-btn-ghost mg-rx-btn mg-rx-add-food" data-add-diet-food>
                    <i class="ri-add-line"></i> Adicionar alimento
                </button>
            </div>
        `);

        mealIndex += 1;
        addFood(mealsContainer.querySelector(`[data-meal-index="${currentMealIndex}"]`));
    };

    builder.addEventListener('click', (event) => {
        const target = event.target instanceof Element ? event.target : null;

        if (!target) {
            return;
        }

        const addMealButton = target.closest('[data-add-diet-meal]');
        if (addMealButton) {
            event.preventDefault();
            addMeal();
            return;
        }

        const addFoodButton = target.closest('[data-add-diet-food]');
        if (addFoodButton) {
            event.preventDefault();
            addFood(addFoodButton.closest('[data-diet-meal]'));
            return;
        }

        const removeMealButton = target.closest('[data-remove-diet-meal]');
        if (removeMealButton) {
            event.preventDefault();
            removeMealButton.closest('[data-diet-meal]')?.remove();
            syncMacros();
            return;
        }

        const removeFoodButton = target.closest('[data-remove-diet-food]');
        if (removeFoodButton) {
            event.preventDefault();
            removeFoodButton.closest('[data-diet-food-row]')?.remove();
            syncMacros();
        }
    });

    builder.addEventListener('input', (event) => {
        const target = event.target instanceof Element ? event.target : null;

        if (!target) {
            return;
        }

        const searchInput = target.closest('[data-diet-food-search]');
        if (searchInput) {
            const row = searchInput.closest('[data-diet-food-row]');
            const select = row?.querySelector('[data-diet-food-select]');

            if (select) {
                refreshFoodOptions(select, searchInput.value);
            }

            syncMacros();
            return;
        }

        if (target.closest('[data-diet-food-grams]')) {
            syncMacros();
        }
    });

    builder.addEventListener('change', (event) => {
        const target = event.target instanceof Element ? event.target : null;

        if (target?.closest('[data-diet-food-select]')) {
            syncMacros();
        }
    });
});
</script>
@endpush
