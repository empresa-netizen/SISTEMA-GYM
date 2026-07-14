@extends('layouts.master')

@section('title', 'Produtos')

@section('content')
@php
    $filterKeys = ['search_value', 'search', 'type', 'status', 'service', 'period', 'includes', 'payment', 'recurrence', 'delivery', 'visibility', 'billing'];
    $filtersOpen = request()->hasAny($filterKeys);
    $primeUrl = route('products.list');
    $durationTypes = [
        'daily' => 'dia(s)',
        'weekly' => 'semana(s)',
        'monthly' => 'mês(es)',
        'quarterly' => 'trimestre(s)',
        'half_yearly' => 'semestre(s)',
        'yearly' => 'ano(s)',
        'lifetime' => 'vitalício',
    ];
    $periodOptions = [
        'daily' => 'Diário',
        'weekly' => 'Semanal',
        'monthly' => 'Mensal',
        'quarterly' => 'Trimestral',
        'half_yearly' => 'Semestral',
        'yearly' => 'Anual',
        'lifetime' => 'Vitalício',
    ];
@endphp

<div class="prime-clients-page prime-products-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Meus produtos</h1>
            <p class="prime-page-sub">Gerencie os seus produtos</p>
        </div>
    </div>

    <div class="prime-products-command">
        <div class="prime-products-urlbox">
            <span class="prime-products-urlbox__label">URL Prime - Todos os planos online</span>
            <code id="primeProductsUrl" class="prime-products-urlbox__value">{{ $primeUrl }}</code>
            <button type="button" class="prime-icon-btn prime-copy-btn" data-copy-target="#primeProductsUrl" title="Copiar URL">
                <i class="ri-file-copy-line"></i>
            </button>
        </div>
        <div class="prime-products-command__actions">
            <a href="{{ route('invoices.create') }}" class="prime-btn-ghost">
                <i class="ri-shopping-cart-line"></i> Simular Venda
            </a>
            <a href="{{ route('products.coupons') }}" class="prime-btn-ghost">
                <i class="ri-coupon-3-line"></i> Cupons
            </a>
            <a href="{{ route('membership-plans.create') }}" class="prime-btn-primary">
                <i class="ri-add-line"></i> Novo produto
            </a>
        </div>
    </div>

    <div class="prime-clients-filters">
        <button type="button" class="prime-btn-ghost prime-filters-toggle {{ $filtersOpen ? 'is-open' : '' }}" data-bs-toggle="collapse" data-bs-target="#primePlansFilters" aria-expanded="{{ $filtersOpen ? 'true' : 'false' }}">
            <i class="ri-filter-3-line"></i> Filtros
            <i class="ri-arrow-down-s-line prime-filters-chevron"></i>
        </button>

        <div class="collapse {{ $filtersOpen ? 'show' : '' }}" id="primePlansFilters">
            <form method="get" action="{{ route('membership-plans.index') }}" class="prime-clients-filters__form">
                <div class="prime-products-filters__grid">
                    <div>
                        <label class="prime-field-label">Buscar</label>
                        <input type="text" name="search_value" value="{{ request('search_value', request('search')) }}" class="prime-field" placeholder="Nome ou descrição...">
                    </div>
                    <div>
                        <label class="prime-field-label">Tipo</label>
                        <select name="type" class="prime-field">
                            <option value="">Todos</option>
                            <option value="plan" @selected(request('type') === 'plan')>Plano online</option>
                            <option value="challenge" @selected(request('type') === 'challenge')>Desafio</option>
                            <option value="consulting" @selected(request('type') === 'consulting')>Consultoria</option>
                        </select>
                    </div>
                    <div>
                        <label class="prime-field-label">Status</label>
                        <select name="status" class="prime-field">
                            <option value="">Todos</option>
                            <option value="active" @selected(request('status') === 'active')>Ativo</option>
                            <option value="inactive" @selected(request('status') === 'inactive')>Inativo</option>
                        </select>
                    </div>
                    <div>
                        <label class="prime-field-label">Atendimento</label>
                        <select name="service" class="prime-field">
                            <option value="">Todos</option>
                            <option value="training" @selected(request('service') === 'training')>Com treino</option>
                            <option value="standard" @selected(request('service') === 'standard')>Padrão</option>
                        </select>
                    </div>
                    <div>
                        <label class="prime-field-label">Período</label>
                        <select name="period" class="prime-field">
                            <option value="">Todos</option>
                            @foreach($periodOptions as $value => $label)
                                <option value="{{ $value }}" @selected(request('period') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="prime-field-label">Inclui</label>
                        <select name="includes" class="prime-field">
                            <option value="">Todos</option>
                            <option value="classes" @selected(request('includes') === 'classes')>Aulas limitadas</option>
                            <option value="unlimited" @selected(request('includes') === 'unlimited')>Aulas ilimitadas</option>
                        </select>
                    </div>
                    <div>
                        <label class="prime-field-label">Pagamento</label>
                        <select name="payment" class="prime-field">
                            <option value="">Todos</option>
                            <option value="pix" @selected(request('payment') === 'pix')>PIX</option>
                            <option value="card" @selected(request('payment') === 'card')>Cartão</option>
                            <option value="boleto" @selected(request('payment') === 'boleto')>Boleto</option>
                        </select>
                    </div>
                    <div>
                        <label class="prime-field-label">Recorrência</label>
                        <select name="recurrence" class="prime-field">
                            <option value="">Todas</option>
                            <option value="recurring" @selected(request('recurrence') === 'recurring')>Recorrente</option>
                            <option value="single" @selected(request('recurrence') === 'single')>Pagamento único</option>
                        </select>
                    </div>
                    <div>
                        <label class="prime-field-label">Entrega</label>
                        <select name="delivery" class="prime-field">
                            <option value="">Todas</option>
                            <option value="online" @selected(request('delivery') === 'online')>Online</option>
                            <option value="hybrid" @selected(request('delivery') === 'hybrid')>Híbrido</option>
                        </select>
                    </div>
                    <div>
                        <label class="prime-field-label">Visibilidade</label>
                        <select name="visibility" class="prime-field">
                            <option value="">Todas</option>
                            <option value="public" @selected(request('visibility') === 'public')>Público</option>
                            <option value="private" @selected(request('visibility') === 'private')>Privado</option>
                        </select>
                    </div>
                    <div>
                        <label class="prime-field-label">Cobrança</label>
                        <select name="billing" class="prime-field">
                            <option value="">Todas</option>
                            <option value="automatic" @selected(request('billing') === 'automatic')>Automática</option>
                            <option value="manual" @selected(request('billing') === 'manual')>Manual</option>
                        </select>
                    </div>
                    <div class="prime-products-filters__actions">
                        <button type="submit" class="prime-btn-primary"><i class="ri-search-line"></i> Aplicar</button>
                        <a href="{{ route('membership-plans.index') }}" class="prime-btn-ghost">Limpar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="prime-products-summary">
        <span>{{ $totalCount ?? $plans->count() }} produtos</span>
        <span>{{ $activeCount ?? 0 }} ativos</span>
        <span>{{ $inactiveCount ?? 0 }} inativos</span>
    </div>

    <div class="prime-product-list">
        @forelse($plans as $plan)
            @php
                $durationLabel = $plan->duration_type === 'lifetime'
                    ? 'Vitalício'
                    : $plan->duration_value.' '.($durationTypes[$plan->duration_type] ?? $plan->duration_type);
                $features = collect($plan->features ?? [])->map(fn ($feature) => mb_strtolower((string) $feature));
                $hasDiet = $features->contains(fn ($feature) => str_contains($feature, 'dieta') || str_contains($feature, 'nutri'));
                $checkoutUrl = route('membership-plans.show', $plan);
                $installmentsLabel = $plan->duration_type === 'lifetime' ? '1x' : max(1, (int) $plan->duration_value).'x';
            @endphp
            <div class="prime-product-row prime-product-card">
                <div class="prime-product-row__check">
                    <input type="checkbox" aria-label="Selecionar {{ $plan->name }}">
                </div>

                <a href="{{ route('membership-plans.show', $plan) }}" class="prime-product-row__main">
                    <div class="prime-product-row__titleline">
                        <strong>{{ $plan->name }}</strong>
                        <span class="prime-chip prime-chip--info">Online</span>
                        @if($plan->is_active)
                            <span class="prime-chip prime-chip--success">Ativo</span>
                        @else
                            <span class="prime-chip">Inativo</span>
                        @endif
                        @if($plan->personal_training)
                            <span class="prime-chip prime-chip--success">Treino</span>
                        @else
                            <span class="prime-chip">Treino</span>
                        @endif
                        @if($hasDiet)
                            <span class="prime-chip prime-chip--success">Dieta</span>
                        @else
                            <span class="prime-chip">Dieta</span>
                        @endif
                        <span class="prime-chip prime-chip--warn">PIX</span>
                        <span class="prime-chip prime-chip--info">Cartão</span>
                        <span class="prime-chip">{{ $installmentsLabel }}</span>
                    </div>
                    <div class="prime-product-row__meta">
                        <span>{{ $plan->members_count }} {{ $plan->members_count === 1 ? 'cliente' : 'clientes' }}</span>
                        <span>{{ $plan->max_classes ? $plan->max_classes.' aulas/mês' : 'Aulas ilimitadas' }}</span>
                        @if($plan->description)
                            <span>{{ \Illuminate\Support\Str::limit($plan->description, 72) }}</span>
                        @endif
                    </div>
                </a>

                <div class="prime-product-row__listing" aria-label="Listagens">
                    <i class="ri-store-2-line" title="Listagem online"></i>
                    <i class="ri-smartphone-line" title="App"></i>
                    <i class="ri-global-line" title="Página pública"></i>
                </div>

                <div class="prime-product-row__price">
                    <strong>R$ {{ number_format($plan->price, 2, ',', '.') }}</strong>
                    <span>{{ $durationLabel }}</span>
                </div>

                <button type="button" class="prime-icon-btn prime-copy-btn" data-copy-value="{{ $checkoutUrl }}" title="Copiar URL do produto">
                    <i class="ri-file-copy-line"></i>
                </button>

                <div class="dropdown">
                    <button type="button" class="prime-icon-btn" data-bs-toggle="dropdown" aria-expanded="false" title="Ações">
                        <i class="ri-more-2-fill"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end prime-dropdown">
                        <li>
                            <a class="dropdown-item" href="{{ route('membership-plans.edit', $plan) }}">
                                <i class="ri-pencil-line me-2"></i> Editar
                            </a>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('membership-plans.duplicate', $plan) }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="ri-file-copy-2-line me-2"></i> Duplicar
                                </button>
                            </form>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('membership-plans.destroy', $plan) }}" onsubmit="return confirm('Excluir este produto?')">
                                @csrf
                                <input type="hidden" name="_response" value="redirect">
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="ri-delete-bin-line me-2"></i> Excluir
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        @empty
            <div class="prime-empty-state">
                <i class="ri-price-tag-3-line"></i>
                <p>Nenhum produto encontrado.</p>
                <a href="{{ route('membership-plans.create') }}" class="prime-btn-primary">Criar produto</a>
            </div>
        @endforelse
    </div>
</div>

<script>
    document.querySelectorAll('.prime-copy-btn').forEach((button) => {
        button.addEventListener('click', async () => {
            const target = button.dataset.copyTarget ? document.querySelector(button.dataset.copyTarget) : null;
            const value = button.dataset.copyValue || target?.textContent?.trim();

            if (!value || !navigator.clipboard) {
                return;
            }

            await navigator.clipboard.writeText(value);
            button.classList.add('is-copied');
            setTimeout(() => button.classList.remove('is-copied'), 1200);
        });
    });
</script>
@endsection
