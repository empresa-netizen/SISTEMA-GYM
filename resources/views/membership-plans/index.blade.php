@extends('layouts.master')

@section('title', 'Produtos')

@section('content')
@php
    $filterKeys = ['search_value', 'search', 'type', 'status', 'service', 'period', 'includes', 'payment', 'recurrence', 'delivery', 'visibility', 'billing'];
    $filtersOpen = request()->hasAny($filterKeys);
    $mgUrl = route('products.list');
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

<div class="mg-clients-page mg-products-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Meus produtos</h1>
            <p class="mg-page-sub">Gerencie os seus produtos</p>
        </div>
    </div>

    <div class="mg-products-command">
        <div class="mg-products-urlbox">
            <span class="mg-products-urlbox__label">URL MGTEAM - Todos os planos online</span>
            <code id="mgProductsUrl" class="mg-products-urlbox__value">{{ $mgUrl }}</code>
            <button type="button" class="mg-icon-btn mg-copy-btn" data-copy-target="#mgProductsUrl" title="Copiar URL">
                <i class="ri-file-copy-line"></i>
            </button>
        </div>
        <div class="mg-products-command__actions">
            <a href="{{ route('invoices.create') }}" class="mg-btn-ghost">
                <i class="ri-shopping-cart-line"></i> Simular Venda
            </a>
            <a href="{{ route('products.coupons') }}" class="mg-btn-ghost">
                <i class="ri-coupon-3-line"></i> Cupons
            </a>
            <a href="{{ route('membership-plans.create') }}" class="mg-btn-primary">
                <i class="ri-add-line"></i> Novo produto
            </a>
        </div>
    </div>

    <div class="mg-clients-filters">
        <button type="button" class="mg-btn-ghost mg-filters-toggle {{ $filtersOpen ? 'is-open' : '' }}" data-bs-toggle="collapse" data-bs-target="#mgPlansFilters" aria-expanded="{{ $filtersOpen ? 'true' : 'false' }}">
            <i class="ri-filter-3-line"></i> Filtros
            <i class="ri-arrow-down-s-line mg-filters-chevron"></i>
        </button>

        <div class="collapse {{ $filtersOpen ? 'show' : '' }}" id="mgPlansFilters">
            <form method="get" action="{{ route('membership-plans.index') }}" class="mg-clients-filters__form">
                <div class="mg-products-filters__grid">
                    <div>
                        <label class="mg-field-label">Buscar</label>
                        <input type="text" name="search_value" value="{{ request('search_value', request('search')) }}" class="mg-field" placeholder="Nome ou descrição...">
                    </div>
                    <div>
                        <label class="mg-field-label">Tipo</label>
                        <select name="type" class="mg-field">
                            <option value="">Todos</option>
                            <option value="plan" @selected(request('type') === 'plan')>Plano online</option>
                            <option value="challenge" @selected(request('type') === 'challenge')>Desafio</option>
                            <option value="consulting" @selected(request('type') === 'consulting')>Consultoria</option>
                        </select>
                    </div>
                    <div>
                        <label class="mg-field-label">Status</label>
                        <select name="status" class="mg-field">
                            <option value="">Todos</option>
                            <option value="active" @selected(request('status') === 'active')>Ativo</option>
                            <option value="inactive" @selected(request('status') === 'inactive')>Inativo</option>
                        </select>
                    </div>
                    <div>
                        <label class="mg-field-label">Atendimento</label>
                        <select name="service" class="mg-field">
                            <option value="">Todos</option>
                            <option value="training" @selected(request('service') === 'training')>Com treino</option>
                            <option value="standard" @selected(request('service') === 'standard')>Padrão</option>
                        </select>
                    </div>
                    <div>
                        <label class="mg-field-label">Período</label>
                        <select name="period" class="mg-field">
                            <option value="">Todos</option>
                            @foreach($periodOptions as $value => $label)
                                <option value="{{ $value }}" @selected(request('period') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mg-field-label">Inclui</label>
                        <select name="includes" class="mg-field">
                            <option value="">Todos</option>
                            <option value="classes" @selected(request('includes') === 'classes')>Aulas limitadas</option>
                            <option value="unlimited" @selected(request('includes') === 'unlimited')>Aulas ilimitadas</option>
                        </select>
                    </div>
                    <div>
                        <label class="mg-field-label">Pagamento</label>
                        <select name="payment" class="mg-field">
                            <option value="">Todos</option>
                            <option value="pix" @selected(request('payment') === 'pix')>PIX</option>
                            <option value="card" @selected(request('payment') === 'card')>Cartão</option>
                            <option value="boleto" @selected(request('payment') === 'boleto')>Boleto</option>
                        </select>
                    </div>
                    <div>
                        <label class="mg-field-label">Recorrência</label>
                        <select name="recurrence" class="mg-field">
                            <option value="">Todas</option>
                            <option value="recurring" @selected(request('recurrence') === 'recurring')>Recorrente</option>
                            <option value="single" @selected(request('recurrence') === 'single')>Pagamento único</option>
                        </select>
                    </div>
                    <div>
                        <label class="mg-field-label">Entrega</label>
                        <select name="delivery" class="mg-field">
                            <option value="">Todas</option>
                            <option value="online" @selected(request('delivery') === 'online')>Online</option>
                            <option value="hybrid" @selected(request('delivery') === 'hybrid')>Híbrido</option>
                        </select>
                    </div>
                    <div>
                        <label class="mg-field-label">Visibilidade</label>
                        <select name="visibility" class="mg-field">
                            <option value="">Todas</option>
                            <option value="public" @selected(request('visibility') === 'public')>Público</option>
                            <option value="private" @selected(request('visibility') === 'private')>Privado</option>
                        </select>
                    </div>
                    <div>
                        <label class="mg-field-label">Cobrança</label>
                        <select name="billing" class="mg-field">
                            <option value="">Todas</option>
                            <option value="automatic" @selected(request('billing') === 'automatic')>Automática</option>
                            <option value="manual" @selected(request('billing') === 'manual')>Manual</option>
                        </select>
                    </div>
                    <div class="mg-products-filters__actions">
                        <button type="submit" class="mg-btn-primary"><i class="ri-search-line"></i> Aplicar</button>
                        <a href="{{ route('membership-plans.index') }}" class="mg-btn-ghost">Limpar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="mg-products-summary">
        <span>{{ $totalCount ?? $plans->count() }} produtos</span>
        <span>{{ $activeCount ?? 0 }} ativos</span>
        <span>{{ $inactiveCount ?? 0 }} inativos</span>
    </div>

    <div class="mg-product-list">
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
            <div class="mg-product-row mg-product-card">
                <div class="mg-product-row__check">
                    <input type="checkbox" aria-label="Selecionar {{ $plan->name }}">
                </div>

                <a href="{{ route('membership-plans.show', $plan) }}" class="mg-product-row__main">
                    <div class="mg-product-row__titleline">
                        <strong>{{ $plan->name }}</strong>
                        <span class="mg-chip mg-chip--info">Online</span>
                        @if($plan->is_active)
                            <span class="mg-chip mg-chip--success">Ativo</span>
                        @else
                            <span class="mg-chip">Inativo</span>
                        @endif
                        @if($plan->personal_training)
                            <span class="mg-chip mg-chip--success">Treino</span>
                        @else
                            <span class="mg-chip">Treino</span>
                        @endif
                        @if($hasDiet)
                            <span class="mg-chip mg-chip--success">Dieta</span>
                        @else
                            <span class="mg-chip">Dieta</span>
                        @endif
                        <span class="mg-chip mg-chip--warn">PIX</span>
                        <span class="mg-chip mg-chip--info">Cartão</span>
                        <span class="mg-chip">{{ $installmentsLabel }}</span>
                    </div>
                    <div class="mg-product-row__meta">
                        <span>{{ $plan->members_count }} {{ $plan->members_count === 1 ? 'cliente' : 'clientes' }}</span>
                        <span>{{ $plan->max_classes ? $plan->max_classes.' aulas/mês' : 'Aulas ilimitadas' }}</span>
                        @if($plan->description)
                            <span>{{ \Illuminate\Support\Str::limit($plan->description, 72) }}</span>
                        @endif
                    </div>
                </a>

                <div class="mg-product-row__listing" aria-label="Listagens">
                    <i class="ri-store-2-line" title="Listagem online"></i>
                    <i class="ri-smartphone-line" title="App"></i>
                    <i class="ri-global-line" title="Página pública"></i>
                </div>

                <div class="mg-product-row__price">
                    <strong>R$ {{ number_format($plan->price, 2, ',', '.') }}</strong>
                    <span>{{ $durationLabel }}</span>
                </div>

                <button type="button" class="mg-icon-btn mg-copy-btn" data-copy-value="{{ $checkoutUrl }}" title="Copiar URL do produto">
                    <i class="ri-file-copy-line"></i>
                </button>

                <div class="dropdown">
                    <button type="button" class="mg-icon-btn" data-bs-toggle="dropdown" aria-expanded="false" title="Ações">
                        <i class="ri-more-2-fill"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end mg-dropdown">
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
            <div class="mg-empty-state">
                <i class="ri-price-tag-3-line"></i>
                <p>Nenhum produto encontrado.</p>
                <a href="{{ route('membership-plans.create') }}" class="mg-btn-primary">Criar produto</a>
            </div>
        @endforelse
    </div>
</div>

<script>
    document.querySelectorAll('.mg-copy-btn').forEach((button) => {
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
