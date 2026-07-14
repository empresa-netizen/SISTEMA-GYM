@php
    $cardioEntries = $member->logbooks->filter(fn ($e) => in_array(strtoupper((string) $e->type), ['TRAINING', 'CARDIO'], true)
        && (str_contains(mb_strtolower($e->title.' '.$e->comment), 'cardio')
            || str_contains(mb_strtolower($e->title.' '.$e->comment), 'corrida')
            || str_contains(mb_strtolower($e->title.' '.$e->comment), 'bike')));
    $cardioPlans = $member->cardioPlans ?? collect();
    $statusLabels = [
        'draft' => 'Rascunho',
        'active' => 'Ativo',
        'completed' => 'Concluído',
        'cancelled' => 'Cancelado',
    ];
@endphp

<div class="mg-tab-block">
    <div class="mg-tab-block__head">
        <div>
            <p class="mg-section-label mb-1">Condicionamento</p>
            <h2 class="mg-tab-block__title">Cardio</h2>
        </div>
        <button type="button" class="mg-btn-primary" data-bs-toggle="modal" data-bs-target="#cardioPlanModal">
            <i class="ri-add-line"></i> Novo plano de cardio
        </button>
    </div>

    <div class="mg-tab-subnav">
        <a href="#cardio-prescriptions" class="mg-tab-subnav__item is-active">Prescrições <span>{{ $cardioPlans->count() }}</span></a>
        <a href="#cardio-records" class="mg-tab-subnav__item">Registros <span>{{ $cardioEntries->count() }}</span></a>
    </div>

    <section id="cardio-prescriptions" class="mg-tab-section">
        <div class="mg-prescription-list">
        @forelse($cardioPlans as $plan)
            <article class="mg-prescription-card">
                <div class="mg-prescription-card__main">
                    <div class="mg-prescription-card__eyebrow">
                        {{ $plan->starts_at?->format('d/m/Y') ?? $plan->created_at?->format('d/m/Y') }}
                    </div>
                    <h3 class="mg-prescription-card__title">{{ $plan->title }}</h3>
                    <div class="mg-prescription-card__meta">
                        @if($plan->modality)<span>{{ $plan->modality }}</span>@endif
                        @if($plan->duration_minutes)<span>{{ $plan->duration_minutes }} min</span>@endif
                        @if($plan->intensity)<span>{{ $plan->intensity }}</span>@endif
                        @if($plan->weekly_frequency)<span>{{ $plan->weekly_frequency }}x/semana</span>@endif
                        @if($plan->notes)<span>{{ \Illuminate\Support\Str::limit($plan->notes, 80) }}</span>@endif
                    </div>
                </div>
                <span class="mg-chip mg-chip--info">{{ $statusLabels[$plan->status] ?? $plan->status }}</span>
            </article>
        @empty
            <div class="mg-empty-state mg-empty-state--compact">
                <i class="ri-heart-pulse-line"></i>
                <p>Nenhum plano de cardio cadastrado</p>
                <button type="button" class="mg-btn-primary" data-bs-toggle="modal" data-bs-target="#cardioPlanModal">Novo plano de cardio</button>
            </div>
        @endforelse
        </div>
    </section>

    <section id="cardio-records" class="mg-tab-section">
        <div class="mg-tab-section__head">
            <h3>Registros de cardio</h3>
        </div>
        <div class="mg-prescription-list">
        @forelse($cardioEntries as $entry)
            <article class="mg-prescription-card">
                <div class="mg-prescription-card__main">
                    <div class="mg-prescription-card__eyebrow">{{ $entry->logged_at?->format('d/m/Y') }}</div>
                    <h3 class="mg-prescription-card__title">{{ $entry->title }}</h3>
                    <div class="mg-prescription-card__meta">
                        @if($entry->rating)<span>{{ $entry->rating }}/5</span>@endif
                        @if($entry->comment)<span>{{ $entry->comment }}</span>@endif
                    </div>
                </div>
                <span class="mg-chip mg-chip--info">Cardio</span>
            </article>
        @empty
            <div class="mg-empty-state mg-empty-state--compact">
                <i class="ri-book-open-line"></i>
                <p>Nenhum registro de cardio ainda.</p>
                <p class="small text-muted mb-0">Registros de corrida, bike ou cardio no diário aparecerão aqui.</p>
            </div>
        @endforelse
        </div>
    </section>
</div>

<div class="modal fade" id="cardioPlanModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('members.cardio.store', $member) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Novo plano de cardio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Título</label>
                    <input type="text" name="title" class="form-control" placeholder="Ex: Zona 2 — 40 min" required>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Modalidade</label>
                        <select name="modality" class="form-select">
                            <option value="">Selecione</option>
                            <option value="Corrida">Corrida</option>
                            <option value="Caminhada">Caminhada</option>
                            <option value="Bike">Bike</option>
                            <option value="Elíptico">Elíptico</option>
                            <option value="Remo">Remo</option>
                            <option value="Natação">Natação</option>
                            <option value="Outro">Outro</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Intensidade</label>
                        <select name="intensity" class="form-select">
                            <option value="">Selecione</option>
                            <option value="Leve">Leve</option>
                            <option value="Moderada">Moderada</option>
                            <option value="Alta">Alta</option>
                            <option value="Intervalado">Intervalado</option>
                        </select>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Duração (min)</label>
                        <input type="number" name="duration_minutes" class="form-control" min="1" max="600" placeholder="40">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Frequência semanal</label>
                        <input type="number" name="weekly_frequency" class="form-control" min="1" max="14" placeholder="3">
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Início</label>
                        <input type="date" name="starts_at" class="form-control" value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fim (opcional)</label>
                        <input type="date" name="ends_at" class="form-control">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Observações</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Orientações para o aluno..."></textarea>
                </div>
                <input type="hidden" name="status" value="active">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar plano</button>
            </div>
        </form>
    </div>
</div>
