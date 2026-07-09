@extends('layouts.master')

@section('title', 'Agenda')

@section('content')
@php
    $filtersOpen = request()->hasAny(['status', 'search']);
    $statusMap = [
        'scheduled' => ['Agendado', 'prime-chip--info'],
        'ongoing' => ['Em andamento', 'prime-chip--success'],
        'completed' => ['Concluído', ''],
        'cancelled' => ['Cancelado', 'prime-chip--danger'],
    ];
@endphp

<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Agenda</h1>
            <div class="prime-clients-counters">
                <span class="prime-clients-counter">
                    <i class="ri-calendar-event-line"></i>
                    {{ $events->total() }} eventos
                </span>
                <span class="prime-clients-counter prime-clients-counter--delivered">
                    <i class="ri-time-line"></i>
                    {{ $upcomingEvents->count() }} próximos
                </span>
            </div>
        </div>
        <div class="prime-clients-toolbar__right">
            <a href="{{ route('events.schedule') }}" class="prime-btn-ghost">
                <i class="ri-calendar-2-line"></i> Calendário
            </a>
            <a href="{{ route('events.create') }}" class="prime-btn-primary">
                <i class="ri-add-line"></i> Novo evento
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($upcomingEvents->count() > 0)
        <div class="prime-agenda-upcoming">
            <div class="prime-panel-label">Próximos</div>
            <div class="prime-agenda-upcoming__grid">
                @foreach($upcomingEvents as $event)
                    <a href="{{ route('events.show', $event) }}" class="prime-agenda-upcoming__card">
                        <div class="prime-agenda-upcoming__title">{{ $event->title }}</div>
                        <div class="prime-agenda-upcoming__meta">
                            <span><i class="ri-calendar-line"></i> {{ $event->start_time->translatedFormat('d M, H:i') }}</span>
                            <span><i class="ri-map-pin-line"></i> {{ $event->location ?? 'A definir' }}</span>
                        </div>
                        @if($event->max_participants)
                            <span class="prime-chip prime-chip--info">{{ $event->available_spots }} vagas</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <div class="prime-clients-filters">
        <button type="button" class="prime-btn-ghost prime-filters-toggle {{ $filtersOpen ? 'is-open' : '' }}" data-bs-toggle="collapse" data-bs-target="#primeEventsFilters" aria-expanded="{{ $filtersOpen ? 'true' : 'false' }}">
            <i class="ri-filter-3-line"></i> Filtros
            <i class="ri-arrow-down-s-line prime-filters-chevron"></i>
        </button>
        <div class="collapse {{ $filtersOpen ? 'show' : '' }}" id="primeEventsFilters">
            <form method="get" action="{{ route('events.index') }}" class="prime-clients-filters__form">
                <div class="prime-clients-filters__grid prime-clients-filters__grid--3">
                    <div>
                        <label class="prime-field-label">Status</label>
                        <select name="status" class="prime-field">
                            <option value="">Todos</option>
                            <option value="scheduled" @selected(request('status') === 'scheduled')>Agendado</option>
                            <option value="ongoing" @selected(request('status') === 'ongoing')>Em andamento</option>
                            <option value="completed" @selected(request('status') === 'completed')>Concluído</option>
                            <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelado</option>
                        </select>
                    </div>
                    <div class="prime-clients-filters__actions">
                        <button type="submit" class="prime-btn-primary">Aplicar</button>
                        <a href="{{ route('events.index') }}" class="prime-btn-ghost">Limpar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="prime-client-list">
        @forelse($events as $event)
            @php
                [$label, $chip] = $statusMap[$event->status] ?? [$event->status, ''];
            @endphp
            <div class="prime-client-card prime-product-card">
                <a href="{{ route('events.show', $event) }}" class="prime-client-card__main text-decoration-none">
                    <div class="prime-client-card__avatar" style="background:linear-gradient(135deg,#0f766e,#14b8a6)">
                        <i class="ri-calendar-event-line"></i>
                    </div>
                    <div class="prime-client-card__identity">
                        <div class="prime-client-card__name">{{ $event->title }}</div>
                        <div class="prime-client-card__meta">
                            <span>{{ $event->start_time->format('d/m/Y H:i') }}</span>
                            <span class="prime-client-card__sep">|</span>
                            <span>{{ $event->location ?? 'Sem local' }}</span>
                            @if($event->max_participants)
                                <span class="prime-client-card__sep">|</span>
                                <span>{{ $event->registered_count }}/{{ $event->max_participants }} participantes</span>
                            @elseif($event->registered_count)
                                <span class="prime-client-card__sep">|</span>
                                <span>{{ $event->registered_count }} participantes</span>
                            @endif
                        </div>
                        <div class="prime-client-chips">
                            <span class="prime-chip {{ $chip }}">{{ $label }}</span>
                        </div>
                    </div>
                </a>
                <div class="prime-client-card__actions prime-product-card__actions">
                    <a href="{{ route('events.edit', $event) }}" class="prime-btn-ghost prime-btn-ghost--sm" title="Editar">
                        <i class="ri-pencil-line"></i>
                    </a>
                    <button type="button" class="prime-btn-ghost prime-btn-ghost--sm prime-btn-danger-ghost" title="Excluir" onclick="deleteEvent({{ $event->id }})">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                    <a href="{{ route('events.show', $event) }}" class="prime-client-card__chevron-link">
                        <i class="ri-arrow-right-s-line prime-client-card__chevron"></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="prime-empty-state">
                <i class="ri-calendar-event-line"></i>
                <p>Nenhum evento cadastrado.</p>
                <a href="{{ route('events.create') }}" class="prime-btn-primary">Criar evento</a>
            </div>
        @endforelse
    </div>

    @if($events->hasPages())
        <div class="prime-pagination">{{ $events->withQueryString()->links() }}</div>
    @endif
</div>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@section('script')
<script>
function deleteEvent(eventId) {
    Swal.fire({
        title: 'Excluir evento?',
        text: 'Esta ação não pode ser desfeita.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim, excluir',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('deleteForm');
            form.action = '/events/' + eventId;
            form.submit();
        }
    });
}
</script>
@endsection
