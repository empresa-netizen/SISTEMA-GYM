@php
    $deliveryBadge = ['PENDING' => 'prime-chip--warn', 'DELIVERED' => 'prime-chip--success', 'LATE' => 'prime-chip--danger'];
    $deliveryLabels = ['PENDING' => 'Pendente', 'DELIVERED' => 'Entregue', 'LATE' => 'Atrasada'];
    $dietLogs = $member->logbooks->filter(fn ($entry) => strtoupper((string) $entry->type) === 'DIET');
    $dietSort = request('diet_sort', 'recent');
    $dietPrescriptions = $dietSort === 'oldest'
        ? $member->dietPrescriptions->sortBy('created_at')
        : $member->dietPrescriptions->sortByDesc('created_at');
@endphp

<div class="prime-tab-block">
    <div class="prime-tab-block__head">
        <div>
            <p class="prime-section-label mb-1">Nutrição</p>
            <h2 class="prime-tab-block__title">Dieta</h2>
        </div>
        <div class="prime-tab-actions">
            <button type="button" class="prime-btn-primary" data-bs-toggle="modal" data-bs-target="#dietModal">
                <i class="ri-add-line"></i> Novo plano alimentar
            </button>
            <button type="button" class="prime-btn-ghost" data-bs-toggle="modal" data-bs-target="#notifyClientModal">
                <i class="ri-notification-3-line"></i> Notificar cliente
            </button>
        </div>
    </div>

    <div class="prime-tab-subnav">
        <a href="#diet-prescriptions" class="prime-tab-subnav__item is-active">Prescrições <span>{{ $dietPrescriptions->count() }}</span></a>
        <a href="#diet-records" class="prime-tab-subnav__item">Registros <span>{{ $dietLogs->count() }}</span></a>
        <form method="GET" action="{{ route('members.show', [$member, 'tab' => 'diet']) }}" class="prime-tab-sort">
            <input type="hidden" name="tab" value="diet">
            <label class="prime-field-label mb-0">Ordenar</label>
            <select name="diet_sort" class="prime-field prime-field--sm" onchange="this.form.submit()">
                <option value="recent" @selected($dietSort === 'recent')>Mais recentes</option>
                <option value="oldest" @selected($dietSort === 'oldest')>Mais antigos</option>
            </select>
        </form>
    </div>

    <section id="diet-prescriptions" class="prime-tab-section">
        <div class="prime-prescription-list">
        @forelse($dietPrescriptions as $diet)
            <article class="prime-prescription-card">
                <div class="prime-prescription-card__main">
                    <div class="prime-prescription-card__eyebrow">{{ $diet->scheduled_at?->format('d/m/Y H:i') ?? $diet->created_at?->format('d/m/Y') }}</div>
                    <h3 class="prime-prescription-card__title">{{ $diet->title }}</h3>
                    <div class="prime-prescription-card__meta">
                        <span>{{ $diet->dietMenu?->name ?? 'Plano personalizado' }}</span>
                        @if($diet->dietMenu?->meals_count)<span>{{ $diet->dietMenu->meals_count }} refeições</span>@endif
                        @if((float) ($diet->dietMenu?->total_calories ?? 0) > 0)<span>{{ (int) $diet->dietMenu->total_calories }} kcal</span>@endif
                    </div>
                </div>
                <div class="prime-card-actions">
                    <span class="prime-chip {{ $deliveryBadge[$diet->delivery_status] ?? '' }}">{{ $deliveryLabels[$diet->delivery_status] ?? $diet->delivery_status }}</span>
                    <button type="button" class="prime-icon-btn" title="Visualizar" disabled><i class="ri-eye-line"></i></button>
                    <button type="button" class="prime-icon-btn" title="Editar" disabled><i class="ri-pencil-line"></i></button>
                    <div class="dropdown">
                        <button class="prime-icon-btn" data-bs-toggle="dropdown" type="button"><i class="ri-more-2-fill"></i></button>
                        <div class="dropdown-menu dropdown-menu-end prime-dropdown">
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
        @empty
            <div class="prime-empty-state prime-empty-state--compact">
                <i class="ri-restaurant-line"></i>
                <p>Nenhum plano alimentar cadastrado.</p>
                <button type="button" class="prime-btn-primary" data-bs-toggle="modal" data-bs-target="#dietModal">Novo plano alimentar</button>
            </div>
        @endforelse
        </div>
    </section>

    <section id="diet-records" class="prime-tab-section">
        <div class="prime-tab-section__head">
            <h3>Registros alimentares</h3>
        </div>
        <div class="prime-prescription-list">
        @forelse($dietLogs as $entry)
            <article class="prime-prescription-card">
                <div class="prime-prescription-card__main">
                    <div class="prime-prescription-card__eyebrow">{{ $entry->logged_at?->format('d/m/Y') }}</div>
                    <h3 class="prime-prescription-card__title">{{ $entry->title }}</h3>
                    <div class="prime-prescription-card__meta">
                        @if($entry->rating)<span>{{ $entry->rating }}/5</span>@endif
                        @if($entry->comment)<span>{{ $entry->comment }}</span>@endif
                    </div>
                </div>
                <span class="prime-chip prime-chip--info">Registro</span>
            </article>
        @empty
            <div class="prime-empty-state prime-empty-state--compact">
                <i class="ri-book-open-line"></i>
                <p>Nenhum registro alimentar encontrado.</p>
            </div>
        @endforelse
        </div>
    </section>
</div>

<div class="modal fade" id="dietModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('members.diet.store', $member) }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Novo plano alimentar</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Título</label>
                    <input type="text" name="title" class="form-control" placeholder="Ex: Cutting semana 2" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Cardápio base (opcional)</label>
                    <select name="diet_menu_id" class="form-select">
                        <option value="">Personalizado</option>
                        @foreach($dietMenus ?? [] as $menu)
                            <option value="{{ $menu->id }}">{{ $menu->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Agendar para</label>
                    <input type="datetime-local" name="scheduled_at" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Observações</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar plano</button>
            </div>
        </form>
    </div>
</div>
