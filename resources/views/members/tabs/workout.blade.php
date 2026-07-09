@php
    $workoutStatusLabels = ['draft' => 'Rascunho', 'active' => 'Ativo', 'completed' => 'Concluído', 'cancelled' => 'Cancelado', 'archived' => 'Arquivado'];
    $workoutStatusClasses = ['active' => 'prime-chip--success', 'completed' => 'prime-chip--info', 'cancelled' => 'prime-chip--danger'];
    $trainingLogs = $member->logbooks->filter(fn ($entry) => strtoupper((string) $entry->type) === 'TRAINING');
    $workoutSort = request('workout_sort', 'recent');
    $workouts = $workoutSort === 'oldest'
        ? $member->workouts->sortBy('updated_at')
        : $member->workouts->sortByDesc('updated_at');
@endphp

<div class="prime-tab-block">
    <div class="prime-tab-block__head">
        <div>
            <p class="prime-section-label mb-1">Prescrição</p>
            <h2 class="prime-tab-block__title">Treinos</h2>
        </div>
        <a href="{{ route('workouts.create', ['member' => $member->id]) }}" class="prime-btn-primary">
            <i class="ri-add-line"></i> Novo plano de treino
        </a>
    </div>

    <div class="prime-tab-subnav">
        <a href="#workout-prescriptions" class="prime-tab-subnav__item is-active">Prescrições <span>{{ $workouts->count() }}</span></a>
        <a href="#workout-records" class="prime-tab-subnav__item">Registros <span>{{ $trainingLogs->count() }}</span></a>
        <form method="GET" action="{{ route('members.show', [$member, 'tab' => 'workout']) }}" class="prime-tab-sort">
            <input type="hidden" name="tab" value="workout">
            <label class="prime-field-label mb-0">Ordenar</label>
            <select name="workout_sort" class="prime-field prime-field--sm" onchange="this.form.submit()">
                <option value="recent" @selected($workoutSort === 'recent')>Mais recentes</option>
                <option value="oldest" @selected($workoutSort === 'oldest')>Mais antigos</option>
            </select>
        </form>
    </div>

    <section id="workout-prescriptions" class="prime-tab-section">
        <div class="prime-prescription-list">
        @forelse($workouts as $workout)
            <article class="prime-prescription-card">
                <div class="prime-prescription-card__main">
                    <div class="prime-prescription-card__eyebrow">Atualizado {{ $workout->updated_at?->format('d/m/Y') }}</div>
                    <h3 class="prime-prescription-card__title">{{ $workout->name }}</h3>
                    <div class="prime-prescription-card__meta">
                        <span>{{ $workout->workout_date ? 'Data: '.$workout->workout_date->format('d/m/Y') : 'Frequência não definida' }}</span>
                        @if($workout->description)<span>{{ Str::limit($workout->description, 70) }}</span>@endif
                    </div>
                </div>
                <div class="prime-card-actions">
                    <span class="prime-chip {{ $workoutStatusClasses[$workout->status] ?? '' }}">{{ $workoutStatusLabels[$workout->status] ?? ucfirst($workout->status) }}</span>
                    <a href="{{ route('workouts.show', $workout) }}" class="prime-icon-btn" title="Visualizar"><i class="ri-eye-line"></i></a>
                    <a href="{{ route('workouts.edit', $workout) }}" class="prime-icon-btn" title="Editar"><i class="ri-pencil-line"></i></a>
                    <button type="button" class="prime-icon-btn" disabled title="Mais opções"><i class="ri-more-2-fill"></i></button>
                </div>
            </article>
        @empty
            <div class="prime-empty-state prime-empty-state--compact">
                <i class="ri-run-line"></i>
                <p>Nenhum plano de treino cadastrado.</p>
                <a href="{{ route('workouts.create', ['member' => $member->id]) }}" class="prime-btn-primary">Novo plano de treino</a>
            </div>
        @endforelse
        </div>
    </section>

    <section id="workout-records" class="prime-tab-section">
        <div class="prime-tab-section__head">
            <h3>Registros de treino</h3>
        </div>
        <div class="prime-prescription-list">
        @forelse($trainingLogs as $entry)
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
                <p>Nenhum registro de treino encontrado.</p>
            </div>
        @endforelse
        </div>
    </section>
</div>
