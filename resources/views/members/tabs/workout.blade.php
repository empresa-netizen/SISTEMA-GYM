@php
    $workoutStatusLabels = ['draft' => 'Rascunho', 'active' => 'Ativo', 'completed' => 'Concluído', 'cancelled' => 'Cancelado', 'archived' => 'Arquivado'];
    $workoutStatusClasses = ['active' => 'mg-chip--success', 'completed' => 'mg-chip--info', 'cancelled' => 'mg-chip--danger'];
    $trainingLogs = $member->logbooks->filter(fn ($entry) => strtoupper((string) $entry->type) === 'TRAINING');
    $workoutSort = request('workout_sort', 'recent');
    $workouts = $workoutSort === 'oldest'
        ? $member->workouts->sortBy('updated_at')
        : $member->workouts->sortByDesc('updated_at');
@endphp

@push('styles')
<style>
    .mg-rx--workout {
        --rx-bg: #020817;
        --rx-panel: #111A2B;
        --rx-panel-soft: #151F32;
        --rx-line: rgba(87, 111, 148, 0.28);
        --rx-line-strong: rgba(112, 139, 178, 0.42);
        --rx-blue: #3B95FF;
        --rx-text: #F6F8FC;
        --rx-muted: #9EAAC0;
    }

    .mg-rx--workout .mg-tab-block__head {
        align-items: center;
        margin-bottom: 1.08rem;
        padding-bottom: 0.85rem;
        border-bottom: 1px solid rgba(72, 94, 126, 0.34);
    }

    .mg-rx--workout .mg-tab-block__title {
        margin: 0;
        color: var(--rx-text);
        font-size: 1.18rem;
        font-weight: 900;
        letter-spacing: -0.025em;
    }

    .mg-rx--workout .mg-rx-subnav {
        height: 4.05rem;
        margin-bottom: 1rem;
        padding: 0.45rem;
        border: 1px solid var(--rx-line);
        border-radius: 0.95rem;
        background: #080E1A !important;
    }

    .mg-rx--workout .mg-tab-subnav__item {
        min-height: 2.86rem;
        padding: 0 1rem;
        border-radius: 0.7rem;
        color: var(--rx-muted) !important;
        font-size: 0.88rem;
        font-weight: 860;
    }

    .mg-rx--workout .mg-tab-subnav__item.is-active {
        background: #202C3E !important;
        color: #EAF2FF !important;
    }

    .mg-workout-builder {
        margin: 0 0 1rem;
        border: 1px solid rgba(59, 149, 255, 0.42);
        border-radius: 1rem;
        background: linear-gradient(180deg, rgba(17, 26, 43, 0.98), rgba(11, 17, 29, 0.98));
        overflow: hidden;
    }

    .mg-workout-builder[hidden] {
        display: none !important;
    }

    .mg-workout-builder__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.82rem 0.95rem;
        border-bottom: 1px solid var(--rx-line);
    }

    .mg-workout-builder__head strong {
        display: block;
        color: var(--rx-text);
        font-size: 0.98rem;
        font-weight: 900;
    }

    .mg-workout-builder__head span {
        color: var(--rx-muted);
        font-size: 0.76rem;
        font-weight: 700;
    }

    .mg-workout-builder__body {
        display: grid;
        grid-template-columns: 1.1fr 10rem 10rem;
        gap: 0.68rem;
        padding: 0.86rem 0.95rem;
    }

    .mg-workout-builder__body .mg-rx-form-span {
        grid-column: 1 / -1;
    }

    .mg-workout-table {
        margin: 0.15rem 0.95rem 0.95rem;
        border: 1px solid var(--rx-line);
        border-radius: 0.82rem;
        overflow: hidden;
    }

    .mg-workout-table__head,
    .mg-workout-row {
        display: grid;
        grid-template-columns: minmax(15rem, 1.2fr) 4.8rem 4.8rem 5.5rem 5.5rem 6.5rem 2.85rem;
        align-items: center;
        gap: 0;
    }

    .mg-workout-table__head {
        min-height: 2.12rem;
        padding: 0 0.65rem;
        background: #202C3E;
        color: #A9B7CD;
        font-size: 0.67rem;
        font-weight: 950;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .mg-workout-row {
        min-height: 2.95rem;
        padding: 0.35rem 0.65rem;
        background: #0D1524;
    }

    .mg-workout-row + .mg-workout-row {
        border-top: 1px solid var(--rx-line);
    }

    .mg-workout-row .mg-field {
        height: 2.12rem;
        min-height: 2.12rem;
        border-radius: 0.48rem;
        border-color: transparent !important;
        background: transparent !important;
        padding: 0.24rem 0.42rem;
        font-size: 0.78rem;
    }

    .mg-workout-row .mg-field:focus {
        background: #081422 !important;
        border-color: rgba(59, 149, 255, 0.58) !important;
    }

    .mg-workout-row .mg-icon-btn {
        width: 2.08rem;
        height: 2.08rem;
        border-radius: 0.5rem;
    }

    .mg-workout-builder__foot {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.65rem;
        padding: 0 0.95rem 0.95rem;
    }

    @media (max-width: 1100px) {
        .mg-workout-builder__body,
        .mg-workout-table__head,
        .mg-workout-row {
            grid-template-columns: 1fr;
        }

        .mg-workout-table__head {
            display: none;
        }
    }
</style>
@endpush

<div class="mg-tab-block mg-rx mg-rx--workout">
    <div class="mg-tab-block__head">
        <div>
            <p class="mg-section-label mb-1">Prescrição</p>
            <h2 class="mg-tab-block__title">Treinos</h2>
        </div>
        <div class="mg-tab-actions mg-rx-actions">
            <button type="button" class="mg-btn-primary mg-rx-btn" data-toggle-workout-builder>
                <i class="ri-add-line"></i> Novo plano de treino
            </button>
            <button type="button" class="mg-btn-ghost mg-rx-btn" data-bs-toggle="modal" data-bs-target="#importWorkoutTemplateModal">
                <i class="ri-sparkling-line"></i> Importar modelo
            </button>
            <button type="button" class="mg-btn-ghost mg-rx-btn" data-bs-toggle="modal" data-bs-target="#notifyClientModal">
                <i class="ri-notification-3-line"></i> Notificar cliente
            </button>
        </div>
    </div>

    <div class="mg-tab-subnav mg-rx-subnav">
        <a href="#workout-prescriptions" class="mg-tab-subnav__item is-active">Prescrições <span>{{ $workouts->count() }}</span></a>
        <a href="#workout-records" class="mg-tab-subnav__item">Registros <span>{{ $trainingLogs->count() }}</span></a>
        <form method="GET" action="{{ route('members.show', [$member, 'tab' => 'workout']) }}" class="mg-tab-sort mg-rx-sort">
            <input type="hidden" name="tab" value="workout">
            <label class="mg-field-label mb-0">Ordenar</label>
            <select name="workout_sort" class="mg-field mg-field--sm" onchange="this.form.submit()">
                <option value="recent" @selected($workoutSort === 'recent')>Mais recentes</option>
                <option value="oldest" @selected($workoutSort === 'oldest')>Mais antigos</option>
            </select>
        </form>
    </div>

    <section class="mg-workout-builder" data-workout-builder hidden>
        <form method="POST" action="{{ route('workouts.store') }}" data-workout-inline-form>
            @csrf
            <input type="hidden" name="member_id" value="{{ $member->id }}">
            <input type="hidden" name="status" value="active">
            <div class="mg-workout-builder__head">
                <div>
                    <strong>Montar treino em linha</strong>
                    <span>Payload direto para WorkoutController: treino + atividades.</span>
                </div>
                <button type="button" class="mg-icon-btn" data-close-workout-builder title="Fechar"><i class="ri-close-line"></i></button>
            </div>
            <div class="mg-workout-builder__body">
                <div>
                    <label class="mg-field-label">Nome do treino</label>
                    <input name="name" class="mg-field mg-rx-input" placeholder="Ex: Treino A — Inferiores" required>
                </div>
                <div>
                    <label class="mg-field-label">Data</label>
                    <input type="date" name="workout_date" class="mg-field mg-rx-input" value="{{ now()->format('Y-m-d') }}">
                </div>
                <div>
                    <label class="mg-field-label">Status</label>
                    <select class="mg-field mg-rx-input" disabled>
                        <option>Ativo</option>
                    </select>
                </div>
                <div class="mg-rx-form-span">
                    <label class="mg-field-label">Descrição ao aluno</label>
                    <input name="description" class="mg-field mg-rx-input" placeholder="Ex: foco em progressão de carga e controle de descanso">
                </div>
                <div class="mg-rx-form-span">
                    <label class="mg-field-label">Observações internas</label>
                    <input name="notes" class="mg-field mg-rx-input" placeholder="Notas visíveis apenas para o treinador">
                </div>
            </div>
            <div class="mg-workout-table" aria-label="Exercícios do novo treino">
                <div class="mg-workout-table__head">
                    <span>Exercício</span>
                    <span>Séries</span>
                    <span>Reps</span>
                    <span>Carga</span>
                    <span>Desc.</span>
                    <span>Duração</span>
                    <span></span>
                </div>
                <div data-workout-rows></div>
            </div>
            <div class="mg-workout-builder__foot">
                <button type="button" class="mg-btn-ghost mg-rx-btn" data-add-workout-row>
                    <i class="ri-add-line"></i> Adicionar exercício
                </button>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="mg-btn-ghost mg-rx-btn" data-close-workout-builder>Cancelar</button>
                    <button type="submit" class="mg-btn-primary mg-rx-btn">
                        <i class="ri-save-line"></i> Salvar
                    </button>
                </div>
            </div>
        </form>
    </section>

    <section id="workout-prescriptions" class="mg-tab-section">
        <div class="mg-prescription-list">
        @forelse($workouts as $workout)
            <article class="mg-prescription-card mg-rx-card">
                <div class="mg-prescription-card__main">
                    <span class="mg-rx-card__icon"><i class="ri-run-line"></i></span>
                    <div class="mg-rx-card__content">
                        <div class="mg-prescription-card__eyebrow">Atualizado {{ $workout->updated_at?->format('d/m/Y') }}</div>
                        <h3 class="mg-prescription-card__title">{{ $workout->name }}</h3>
                        <div class="mg-prescription-card__meta">
                            <span>{{ $workout->workout_date ? 'Data: '.$workout->workout_date->format('d/m/Y') : 'Frequência não definida' }}</span>
                            <span>{{ $workout->activities->count() }} exercícios</span>
                            @if($workout->description)<span>{{ Str::limit($workout->description, 70) }}</span>@endif
                        </div>
                        @if($workout->activities->isNotEmpty())
                            <div class="mg-rx-workout-grid" role="table" aria-label="Exercícios do treino">
                                <div class="mg-rx-workout-grid__head" role="row">
                                    <span>Exercício</span>
                                    <span>Séries</span>
                                    <span>Reps</span>
                                    <span>Carga</span>
                                    <span>Desc.</span>
                                </div>
                                @foreach($workout->activities->take(6) as $activity)
                                    <div class="mg-rx-workout-grid__row" role="row">
                                        <span title="{{ $activity->exercise_name }}">{{ $activity->exercise_name }}</span>
                                        <span>{{ $activity->sets ?: '—' }}</span>
                                        <span>{{ $activity->reps ?: '—' }}</span>
                                        <span>{{ $activity->weight_kg ? rtrim(rtrim(number_format((float) $activity->weight_kg, 1, ',', ''), '0'), ',').'kg' : '—' }}</span>
                                        <span>{{ $activity->rest_seconds ? $activity->rest_seconds.'s' : '—' }}</span>
                                    </div>
                                @endforeach
                                @if($workout->activities->count() > 6)
                                    <div class="mg-rx-workout-grid__more">+{{ $workout->activities->count() - 6 }} exercícios</div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
                <div class="mg-card-actions">
                    <span class="mg-chip mg-rx-status {{ $workoutStatusClasses[$workout->status] ?? '' }}">{{ $workoutStatusLabels[$workout->status] ?? ucfirst($workout->status) }}</span>
                    <a href="{{ route('workouts.show', $workout) }}" class="mg-icon-btn" title="Visualizar"><i class="ri-eye-line"></i></a>
                    <a href="{{ route('workouts.edit', $workout) }}" class="mg-icon-btn" title="Editar"><i class="ri-pencil-line"></i></a>
                </div>
            </article>
        @empty
            <div class="mg-empty-state mg-empty-state--compact mg-rx-empty">
                <i class="ri-run-line"></i>
                <p>Nenhum plano de treino cadastrado.</p>
                <div class="d-flex flex-wrap gap-2 justify-content-center">
                    <button type="button" class="mg-btn-ghost mg-rx-btn" data-bs-toggle="modal" data-bs-target="#importWorkoutTemplateModal">Importar modelo</button>
                    <a href="{{ route('workouts.create', ['member' => $member->id]) }}" class="mg-btn-primary mg-rx-btn">Novo plano de treino</a>
                </div>
            </div>
        @endforelse
        </div>
    </section>

    <section id="workout-records" class="mg-tab-section">
        <div class="mg-tab-section__head">
            <h3>Registros de treino</h3>
        </div>
        <div class="mg-prescription-list">
        @forelse($trainingLogs as $entry)
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
                <p>Nenhum registro de treino encontrado.</p>
            </div>
        @endforelse
        </div>
    </section>
</div>

<div class="modal fade" id="importWorkoutTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered mg-rx-modal">
        <form method="POST" action="{{ isset($workoutTemplates) && $workoutTemplates->isNotEmpty() ? route('workout-templates.assign', $workoutTemplates->first()) : '#' }}" class="modal-content" data-workout-import-form>
            @csrf
            <input type="hidden" name="member_id" value="{{ $member->id }}">
            <div class="modal-header">
                <h5 class="modal-title">Importar treino da biblioteca</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if(isset($workoutTemplates) && $workoutTemplates->isNotEmpty())
                    <p class="text-muted small">O template será clonado para este aluno. Alterações futuras no template não mudam a prescrição salva.</p>
                    <div class="mb-3">
                        <label class="mg-field-label">Template</label>
                        <select class="mg-field mg-rx-input" data-workout-template-select required>
                            @foreach($workoutTemplates as $template)
                                <option
                                    value="{{ route('workout-templates.assign', $template) }}"
                                    data-summary="{{ $template->activities->count() }} exercícios{{ $template->focus ? ' · '.$template->focus : '' }}"
                                >
                                    {{ $template->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mg-rx-alert" data-workout-template-summary></div>
                @else
                    <div class="mg-empty-state mg-empty-state--compact mg-rx-empty">
                        <i class="ri-list-check-3"></i>
                        <p>Nenhum template publicado na biblioteca.</p>
                        <a href="{{ route('workout-templates.index') }}" class="mg-btn-primary mg-rx-btn">Criar template</a>
                    </div>
                @endif
            </div>
            @if(isset($workoutTemplates) && $workoutTemplates->isNotEmpty())
                <div class="modal-footer">
                    <button type="button" class="mg-btn-ghost mg-rx-btn" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="mg-btn-primary mg-rx-btn">Importar para {{ $member->name }}</button>
                </div>
            @endif
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-workout-import-form]');
    const select = document.querySelector('[data-workout-template-select]');
    const summary = document.querySelector('[data-workout-template-summary]');
    const inlineBuilder = document.querySelector('[data-workout-builder]');
    const inlineForm = document.querySelector('[data-workout-inline-form]');
    const workoutRows = inlineForm?.querySelector('[data-workout-rows]');
    let workoutRowIndex = 0;

    const workoutRowTemplate = (index) => `
        <div class="mg-workout-row" data-workout-row>
            <input class="mg-field" name="activities[${index}][exercise_name]" placeholder="Exercício" required>
            <input class="mg-field" type="number" name="activities[${index}][sets]" min="1" placeholder="3">
            <input class="mg-field" type="number" name="activities[${index}][reps]" min="1" placeholder="12">
            <input class="mg-field" type="number" name="activities[${index}][weight_kg]" min="0" step="0.5" placeholder="kg">
            <input class="mg-field" type="number" name="activities[${index}][rest_seconds]" min="0" value="60" placeholder="60s">
            <input class="mg-field" type="number" name="activities[${index}][duration_minutes]" min="1" placeholder="min">
            <button type="button" class="mg-icon-btn" data-remove-workout-row title="Remover exercício"><i class="ri-delete-bin-line"></i></button>
            <input type="hidden" name="activities[${index}][description]" value="">
        </div>
    `;

    const addWorkoutRow = () => {
        if (!workoutRows) {
            return;
        }

        workoutRows.insertAdjacentHTML('beforeend', workoutRowTemplate(workoutRowIndex));
        workoutRowIndex += 1;
    };

    const openWorkoutBuilder = () => {
        if (!inlineBuilder) {
            return;
        }

        inlineBuilder.hidden = false;

        if (workoutRows && workoutRows.children.length === 0) {
            addWorkoutRow();
        }

        inlineBuilder.querySelector('input[name="name"]')?.focus();
    };

    document.querySelectorAll('[data-toggle-workout-builder]').forEach((button) => {
        button.addEventListener('click', openWorkoutBuilder);
    });

    document.querySelectorAll('[data-close-workout-builder]').forEach((button) => {
        button.addEventListener('click', () => {
            if (inlineBuilder) {
                inlineBuilder.hidden = true;
            }
        });
    });

    inlineForm?.addEventListener('click', (event) => {
        const target = event.target instanceof Element ? event.target : null;

        if (target?.closest('[data-add-workout-row]')) {
            event.preventDefault();
            addWorkoutRow();
            return;
        }

        if (target?.closest('[data-remove-workout-row]')) {
            event.preventDefault();
            target.closest('[data-workout-row]')?.remove();

            if (workoutRows && workoutRows.children.length === 0) {
                addWorkoutRow();
            }
        }
    });

    if (!form || !select) {
        return;
    }

    const syncWorkoutTemplate = () => {
        const option = select.options[select.selectedIndex];
        form.action = option.value;
        if (summary) {
            summary.textContent = option.dataset.summary || 'Template pronto para importação.';
        }
    };

    select.addEventListener('change', syncWorkoutTemplate);
    syncWorkoutTemplate();
});
</script>
@endpush
