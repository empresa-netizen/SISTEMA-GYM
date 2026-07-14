@extends('layouts.master')

@section('title', 'Treinos Predefinidos')

@php
    $hasFilters = ($filters['q'] ?? '') !== ''
        || ($filters['status'] ?? '') !== ''
        || ($filters['level'] ?? '') !== '';
@endphp

@push('styles')
    <style>
        .prime-template-page {
            display: grid;
            gap: 0.85rem;
        }

        .prime-template-grid {
            display: grid;
            gap: 0.62rem;
        }

        .prime-template-card {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 1rem;
            align-items: center;
            padding: 0.78rem 0.92rem;
            border: 1px solid #D8E0EA;
            border-radius: 0.78rem;
            background: #FFFFFF;
            box-shadow: 0 8px 22px rgba(23, 37, 56, 0.045);
        }

        .prime-template-card__main {
            display: flex;
            min-width: 0;
            gap: 0.72rem;
            align-items: center;
        }

        .prime-template-card__icon {
            display: grid;
            width: 2.48rem;
            height: 2.48rem;
            flex: 0 0 2.48rem;
            place-items: center;
            border-radius: 0.72rem;
            background: linear-gradient(135deg, #15365F, #3B95FF);
            color: #FFFFFF;
            font-size: 1.16rem;
        }

        .prime-template-card__title {
            overflow: hidden;
            color: #101929;
            font-size: 0.96rem;
            font-weight: 870;
            line-height: 1.18;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .prime-template-card__meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.28rem 0.58rem;
            margin-top: 0.24rem;
            color: #708098;
            font-size: 0.76rem;
            font-weight: 720;
        }

        .prime-template-card__actions {
            min-width: 23rem;
        }

        .prime-template-assign {
            display: grid;
            grid-template-columns: minmax(10rem, 1fr) auto;
            gap: 0.45rem;
            align-items: center;
        }

        .prime-builder-modal .modal-dialog {
            max-width: min(1050px, calc(100vw - 2rem));
        }

        .prime-builder-modal .modal-content {
            overflow: hidden;
            border: 1px solid #D7DFEA;
            border-radius: 1rem;
            background: #F6F8FB;
            box-shadow: 0 24px 80px rgba(15, 28, 46, 0.24);
        }

        .prime-builder-modal .modal-header,
        .prime-builder-modal .modal-footer {
            padding: 0.78rem 1rem;
            border-color: #DDE5EF;
            background: #FFFFFF;
        }

        .prime-builder-modal .modal-title {
            color: #101929;
            font-size: 0.98rem;
            font-weight: 900;
        }

        .prime-builder-modal .modal-body {
            padding: 0.92rem;
        }

        .prime-builder-section {
            padding: 0.78rem;
            border: 1px solid #DDE5EF;
            border-radius: 0.82rem;
            background: #FFFFFF;
        }

        .prime-builder-section + .prime-builder-section {
            margin-top: 0.72rem;
        }

        .prime-builder-head {
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
            align-items: center;
            margin-bottom: 0.6rem;
        }

        .prime-builder-title {
            margin: 0;
            color: #142136;
            font-size: 0.83rem;
            font-weight: 900;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        .prime-builder-hint {
            margin: 0.12rem 0 0;
            color: #7B8BA4;
            font-size: 0.74rem;
            font-weight: 650;
        }

        .prime-workout-builder {
            display: grid;
            gap: 0.42rem;
        }

        .prime-workout-builder__header,
        .prime-workout-builder__row {
            display: grid;
            grid-template-columns: minmax(13rem, 1.5fr) 4.2rem 4.7rem 5.3rem 5.6rem minmax(9rem, 1fr) 2.4rem;
            gap: 0.42rem;
            align-items: center;
        }

        .prime-workout-builder__header {
            color: #7D8CA2;
            font-size: 0.68rem;
            font-weight: 900;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .prime-workout-builder__row {
            padding: 0.38rem;
            border: 1px solid #E1E8F1;
            border-radius: 0.7rem;
            background: #FBFCFE;
        }

        .prime-template-empty-builder {
            padding: 0.74rem;
            border: 1px dashed #C7D3E2;
            border-radius: 0.76rem;
            color: #75859A;
            font-size: 0.78rem;
            font-weight: 720;
            text-align: center;
        }

        @media (max-width: 991.98px) {
            .prime-template-card {
                grid-template-columns: 1fr;
            }

            .prime-template-card__actions {
                min-width: 0;
            }

            .prime-template-assign {
                grid-template-columns: 1fr;
            }

            .prime-workout-builder__header {
                display: none;
            }

            .prime-workout-builder__row {
                grid-template-columns: 1fr 1fr;
            }

            .prime-workout-builder__row > :first-child,
            .prime-workout-builder__row > :nth-child(6) {
                grid-column: 1 / -1;
            }
        }
    </style>
@endpush

@section('content')
<div class="prime-clients-page prime-template-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Treinos Predefinidos</h1>
            <div class="prime-clients-counters">
                <span class="prime-clients-counter">
                    <i class="ri-list-check-3"></i>
                    {{ $templates->total() }} templates
                </span>
            </div>
        </div>
        <div class="prime-clients-toolbar__right">
            <a href="{{ route('library.workout') }}" class="prime-btn-ghost">
                <i class="ri-arrow-left-line"></i> Voltar
            </a>
            <button type="button" class="prime-btn-primary" data-bs-toggle="modal" data-bs-target="#workoutTemplateModal">
                <i class="ri-add-line"></i> Novo template
            </button>
        </div>
    </div>

    <p class="prime-page-sub mb-0">Modelos reutilizáveis da biblioteca, com criação inline de exercícios e importação direta para alunos.</p>

    <form method="GET" action="{{ route('workout-templates.index') }}" class="prime-panel prime-panel--compact">
        <div class="row g-2 align-items-end">
            <div class="col-lg-4 col-md-6">
                <label class="prime-field-label">Buscar</label>
                <input type="search" name="q" value="{{ $filters['q'] }}" class="prime-field" placeholder="Título ou foco">
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="prime-field-label">Status</label>
                <select name="status" class="prime-field">
                    <option value="">Todos</option>
                    @foreach($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 col-md-6">
                <label class="prime-field-label">Nível</label>
                <select name="level" class="prime-field">
                    <option value="">Todos</option>
                    @foreach($levelLabels as $value => $label)
                        <option value="{{ $value }}" @selected($filters['level'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-6 d-flex gap-2">
                <button class="prime-btn-primary w-100" type="submit"><i class="ri-filter-3-line"></i> Filtrar</button>
                @if($hasFilters)
                    <a href="{{ route('workout-templates.index') }}" class="prime-btn-ghost" title="Limpar"><i class="ri-close-line"></i></a>
                @endif
            </div>
        </div>
    </form>

    <div class="prime-template-grid">
        @forelse($templates as $template)
            <div class="prime-template-card">
                <div class="prime-template-card__main">
                    <div class="prime-template-card__icon">
                        <i class="ri-run-line"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="prime-template-card__title">{{ $template->title }}</div>
                        <div class="prime-template-card__meta">
                            @if($template->focus)<span>{{ $template->focus }}</span>@endif
                            @if($template->duration_weeks)<span>{{ $template->duration_weeks }} semanas</span>@endif
                            @if($template->sessions_per_week)<span>{{ $template->sessions_per_week }}x/semana</span>@endif
                            <span>{{ $levelLabels[$template->level] ?? $template->level }}</span>
                            <span class="prime-chip {{ $template->status === 'published' ? 'prime-chip--success' : '' }}">
                                {{ $statusLabels[$template->status] ?? $template->status }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="prime-template-card__actions">
                    <form method="POST" action="{{ route('workout-templates.assign', $template) }}" class="prime-template-assign">
                        @csrf
                        <select name="member_id" class="prime-field prime-field--sm" required>
                            <option value="">Atribuir a aluno...</option>
                            @foreach($members as $member)
                                <option value="{{ $member->id }}">{{ $member->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="prime-btn-ghost" title="Importar para aluno">
                            <i class="ri-user-shared-line"></i> Importar
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="prime-empty-state">
                <i class="ri-list-check-3"></i>
                <p>Nenhum template de treino na biblioteca.</p>
                <button type="button" class="prime-btn-primary" data-bs-toggle="modal" data-bs-target="#workoutTemplateModal">
                    <i class="ri-add-line"></i> Criar primeiro template
                </button>
            </div>
        @endforelse
    </div>

    @if($templates->hasPages())
        <div class="prime-pagination">{{ $templates->links() }}</div>
    @endif
</div>

<div class="modal fade prime-builder-modal" id="workoutTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <form method="POST" action="{{ route('workout-templates.store') }}" class="modal-content" data-workout-template-form>
            @csrf
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">Novo template de treino</h5>
                    <p class="prime-builder-hint">Use a mesma lógica compacta da prescrição individual.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="prime-builder-section">
                    <div class="prime-builder-head">
                        <div>
                            <h6 class="prime-builder-title">Dados do template</h6>
                            <p class="prime-builder-hint">Nome, foco, nível e disponibilidade na biblioteca.</p>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-lg-4">
                            <label class="prime-field-label">Título</label>
                            <input type="text" name="title" class="prime-field" value="{{ old('title') }}" placeholder="Ex: Full Body 3x" required>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="prime-field-label">Foco</label>
                            <input type="text" name="focus" class="prime-field" value="{{ old('focus') }}" placeholder="Hipertrofia, força...">
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label class="prime-field-label">Semanas</label>
                            <input type="number" name="duration_weeks" class="prime-field" min="1" max="52" value="{{ old('duration_weeks', 4) }}">
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label class="prime-field-label">Sessões/semana</label>
                            <input type="number" name="sessions_per_week" class="prime-field" min="1" max="14" value="{{ old('sessions_per_week', 3) }}">
                        </div>
                        <div class="col-lg-1 col-md-6">
                            <label class="prime-field-label">Status</label>
                            <select name="status" class="prime-field">
                                <option value="draft" @selected(old('status') === 'draft')>Rasc.</option>
                                <option value="published" @selected(old('status', 'published') === 'published')>Pub.</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="prime-field-label">Nível</label>
                            <select name="level" class="prime-field">
                                @foreach($levelLabels as $value => $label)
                                    <option value="{{ $value }}" @selected(old('level', 'intermediate') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="prime-field-label">Descrição</label>
                            <input name="description" class="prime-field" value="{{ old('description') }}" placeholder="Objetivo do bloco">
                        </div>
                        <div class="col-md-4">
                            <label class="prime-field-label">Notas internas</label>
                            <input name="notes" class="prime-field" value="{{ old('notes') }}" placeholder="Progressão, cuidado técnico...">
                        </div>
                    </div>
                </div>

                <div class="prime-builder-section">
                    <div class="prime-builder-head">
                        <div>
                            <h6 class="prime-builder-title">Exercícios do template</h6>
                            <p class="prime-builder-hint">Linhas enxutas, como no Prime: exercício, séries, repetições, carga e descanso.</p>
                        </div>
                        <button type="button" class="prime-btn-ghost" data-workout-template-add>
                            <i class="ri-add-line"></i> Adicionar exercício
                        </button>
                    </div>
                    <div class="prime-workout-builder">
                        <div class="prime-workout-builder__header">
                            <span>Exercício</span>
                            <span>Séries</span>
                            <span>Reps</span>
                            <span>Carga</span>
                            <span>Descanso</span>
                            <span>Notas</span>
                            <span></span>
                        </div>
                        <div data-workout-template-rows></div>
                        <div class="prime-template-empty-builder d-none" data-workout-template-empty>
                            Nenhum exercício adicionado. Clique em “Adicionar exercício”.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="prime-btn-ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="prime-btn-primary">Salvar template</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        (function () {
            const form = document.querySelector('[data-workout-template-form]');
            const rowsContainer = document.querySelector('[data-workout-template-rows]');
            const emptyState = document.querySelector('[data-workout-template-empty]');
            const initialActivities = @json(array_values(old('activities', [])));
            let nextActivityIndex = 0;

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

            function activityTemplate(index, activity = {}) {
                return `
                    <div class="prime-workout-builder__row" data-template-activity-row>
                        <input name="activities[${index}][exercise_name]" class="prime-field prime-field--sm" value="${escapeAttribute(activity.exercise_name)}" placeholder="Exercício" required>
                        <input name="activities[${index}][sets]" class="prime-field prime-field--sm" type="number" min="0" value="${escapeAttribute(activity.sets ?? 3)}" placeholder="3">
                        <input name="activities[${index}][reps]" class="prime-field prime-field--sm" type="number" min="0" value="${escapeAttribute(activity.reps ?? 12)}" placeholder="12">
                        <input name="activities[${index}][weight_kg]" class="prime-field prime-field--sm" type="number" min="0" step="0.5" value="${escapeAttribute(activity.weight_kg)}" placeholder="kg">
                        <input name="activities[${index}][rest_seconds]" class="prime-field prime-field--sm" type="number" min="0" value="${escapeAttribute(activity.rest_seconds ?? 60)}" placeholder="seg">
                        <input name="activities[${index}][notes]" class="prime-field prime-field--sm" value="${escapeAttribute(activity.notes)}" placeholder="Cadência, técnica, RIR...">
                        <button type="button" class="prime-icon-btn" data-workout-template-remove title="Remover">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                        <input type="hidden" name="activities[${index}][duration_minutes]" value="${escapeAttribute(activity.duration_minutes)}">
                        <input type="hidden" name="activities[${index}][description]" value="${escapeAttribute(activity.description)}">
                    </div>
                `;
            }

            function syncEmptyState() {
                const hasRows = rowsContainer.querySelector('[data-template-activity-row]') !== null;
                emptyState.classList.toggle('d-none', hasRows);
            }

            function addActivity(activity = {}) {
                rowsContainer.insertAdjacentHTML('beforeend', activityTemplate(nextActivityIndex, activity));
                nextActivityIndex += 1;
                syncEmptyState();
            }

            if (!form || !rowsContainer) {
                return;
            }

            if (initialActivities.length) {
                initialActivities.forEach((activity) => addActivity(activity));
            } else {
                addActivity();
            }

            form.addEventListener('click', function (event) {
                const addButton = event.target.closest('[data-workout-template-add]');
                const removeButton = event.target.closest('[data-workout-template-remove]');

                if (addButton) {
                    addActivity();
                }

                if (removeButton) {
                    removeButton.closest('[data-template-activity-row]')?.remove();
                    syncEmptyState();
                }
            });
        })();
    </script>
@endpush
