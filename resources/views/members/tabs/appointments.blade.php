@php
    $statusLabels = [
        'scheduled' => ['Agendado', 'prime-chip--info'],
        'ongoing' => ['Em andamento', 'prime-chip--success'],
        'completed' => ['Concluído', ''],
        'cancelled' => ['Cancelado', 'prime-chip--danger'],
    ];
    $period = request('period', 'all');
    $statusFilter = request('appointment_status', 'all');
    $typeFilter = request('appointment_type', 'all');
    $typeKeywords = [
        'feedback' => ['feedback', 'retorno'],
        'review' => ['avaliação', 'avaliacao', 'review'],
        'training' => ['treino', 'training'],
    ];
    $filteredAppointments = $member->appointments
        ->filter(function ($event) use ($period) {
            return match ($period) {
                'future' => $event->start_time?->isFuture(),
                'past' => $event->start_time?->isPast(),
                'today' => $event->start_time?->isToday(),
                default => true,
            };
        })
        ->filter(fn ($event) => $statusFilter === 'all' || $event->status === $statusFilter)
        ->filter(function ($event) use ($typeFilter, $typeKeywords) {
            if ($typeFilter === 'all') {
                return true;
            }
            $haystack = mb_strtolower(trim($event->title.' '.$event->description.' '.$event->location));
            if ($typeFilter === 'other') {
                return collect($typeKeywords)->flatten()->every(fn ($keyword) => ! str_contains($haystack, $keyword));
            }
            return collect($typeKeywords[$typeFilter] ?? [])->contains(fn ($keyword) => str_contains($haystack, $keyword));
        });
@endphp

<div class="prime-tab-block">
    <div class="prime-tab-block__head">
        <div>
            <p class="prime-section-label mb-1">Agenda</p>
            <h2 class="prime-tab-block__title">Agendamentos</h2>
        </div>
        <div class="prime-tab-actions">
            <a href="{{ route('events.create', ['member' => $member->id]) }}" class="prime-btn-primary">
                <i class="ri-add-line"></i> Agendamento único
            </a>
            <a href="{{ route('events.create', ['member' => $member->id]) }}" class="prime-btn-ghost">
                <i class="ri-calendar-event-line"></i> Agendar para o período
            </a>
            <a href="{{ route('members.show', [$member, 'tab' => 'appointments', 'period' => 'future']) }}" class="prime-btn-ghost">
                <i class="ri-calendar-check-line"></i> Ver futuros
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('members.show', [$member, 'tab' => 'appointments']) }}" class="prime-tab-filters mb-3">
        <input type="hidden" name="tab" value="appointments">
        <div>
            <label class="prime-field-label">Período</label>
            <select name="period" class="prime-field" onchange="this.form.submit()">
                <option value="all" @selected($period === 'all')>Todos</option>
                <option value="future" @selected($period === 'future')>Futuros</option>
                <option value="today" @selected($period === 'today')>Hoje</option>
                <option value="past" @selected($period === 'past')>Passados</option>
            </select>
        </div>
        <div>
            <label class="prime-field-label">Status</label>
            <select name="appointment_status" class="prime-field" onchange="this.form.submit()">
                <option value="all" @selected($statusFilter === 'all')>Todos</option>
                @foreach($statusLabels as $status => [$label])
                    <option value="{{ $status }}" @selected($statusFilter === $status)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="prime-field-label">Tipo</label>
            <select name="appointment_type" class="prime-field" onchange="this.form.submit()">
                <option value="all" @selected($typeFilter === 'all')>Todos</option>
                <option value="feedback" @selected($typeFilter === 'feedback')>Feedback</option>
                <option value="review" @selected($typeFilter === 'review')>Avaliação</option>
                <option value="training" @selected($typeFilter === 'training')>Treino</option>
                <option value="other" @selected($typeFilter === 'other')>Outros</option>
            </select>
        </div>
        <div class="prime-tab-filters__actions">
            <a href="{{ route('members.show', [$member, 'tab' => 'appointments']) }}" class="prime-btn-ghost">Limpar</a>
        </div>
    </form>

    <div class="prime-prescription-list">
    @forelse($filteredAppointments as $event)
        @php [$statusLabel, $statusClass] = $statusLabels[$event->status] ?? [ucfirst($event->status), '']; @endphp
        <article class="prime-prescription-card">
            <div class="prime-prescription-card__main">
                <div class="prime-prescription-card__eyebrow">{{ $event->start_time?->format('d/m/Y H:i') }}</div>
                <h3 class="prime-prescription-card__title">{{ $event->title }}</h3>
                <div class="prime-prescription-card__meta">
                    @if($event->location)<span><i class="ri-map-pin-line"></i> {{ $event->location }}</span>@endif
                    @if($event->end_time)<span><i class="ri-time-line"></i> até {{ $event->end_time->format('H:i') }}</span>@endif
                </div>
            </div>
            <div class="prime-card-actions">
                <span class="prime-chip {{ $statusClass }}">{{ $event->status === 'scheduled' ? 'Pendente' : $statusLabel }}</span>
                <a href="{{ route('events.edit', $event) }}" class="prime-icon-btn" title="Editar"><i class="ri-pencil-line"></i></a>
                <form method="POST" action="{{ route('events.destroy', $event) }}" onsubmit="return confirm('Excluir este agendamento?')">
                    @csrf
                    @method('DELETE')
                    <button class="prime-icon-btn prime-btn-danger-ghost" title="Excluir"><i class="ri-delete-bin-line"></i></button>
                </form>
            </div>
        </article>
    @empty
        <div class="prime-empty-state prime-empty-state--compact">
            <i class="ri-calendar-check-line"></i>
            <p>Nenhum agendamento encontrado.</p>
            <a href="{{ route('events.create', ['member' => $member->id]) }}" class="prime-btn-primary">Agendamento único</a>
        </div>
    @endforelse
    </div>
</div>
