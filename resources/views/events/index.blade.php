@extends('layouts.master')

@section('title', 'Agenda')

@section('content')
@php
    $filtersOpen = request()->hasAny(['status', 'search']);
    $statusMap = [
        'scheduled' => ['Agendado', 'mg-chip--info'],
        'ongoing' => ['Em andamento', 'mg-chip--success'],
        'completed' => ['Concluído', ''],
        'cancelled' => ['Cancelado', 'mg-chip--danger'],
    ];
@endphp

<div class="mg-clients-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Agenda</h1>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter">
                    <i class="ri-calendar-event-line"></i>
                    {{ $events->total() }} eventos
                </span>
                <span class="mg-clients-counter mg-clients-counter--delivered">
                    <i class="ri-time-line"></i>
                    {{ $upcomingEvents->count() }} próximos
                </span>
            </div>
        </div>
        <div class="mg-clients-toolbar__right">
            <a href="{{ route('events.schedule') }}" class="mg-btn-ghost">
                <i class="ri-calendar-2-line"></i> Calendário
            </a>
            <a href="{{ route('events.create') }}" class="mg-btn-primary">
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
        <div class="mg-agenda-upcoming">
            <div class="mg-panel-label">Próximos</div>
            <div class="mg-agenda-upcoming__grid">
                @foreach($upcomingEvents as $event)
                    <a href="{{ route('events.show', $event) }}" class="mg-agenda-upcoming__card">
                        <div class="mg-agenda-upcoming__title">{{ $event->title }}</div>
                        <div class="mg-agenda-upcoming__meta">
                            <span><i class="ri-calendar-line"></i> {{ $event->start_time->translatedFormat('d M, H:i') }}</span>
                            <span><i class="ri-map-pin-line"></i> {{ $event->location ?? 'A definir' }}</span>
                        </div>
                        @if($event->max_participants)
                            <span class="mg-chip mg-chip--info">{{ $event->available_spots }} vagas</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <div class="mg-clients-filters">
        <button type="button" class="mg-btn-ghost mg-filters-toggle {{ $filtersOpen ? 'is-open' : '' }}" data-bs-toggle="collapse" data-bs-target="#mgEventsFilters" aria-expanded="{{ $filtersOpen ? 'true' : 'false' }}">
            <i class="ri-filter-3-line"></i> Filtros
            <i class="ri-arrow-down-s-line mg-filters-chevron"></i>
        </button>
        <div class="collapse {{ $filtersOpen ? 'show' : '' }}" id="mgEventsFilters">
            <form method="get" action="{{ route('events.index') }}" class="mg-clients-filters__form">
                <div class="mg-clients-filters__grid mg-clients-filters__grid--3">
                    <div>
                        <label class="mg-field-label">Status</label>
                        <select name="status" class="mg-field">
                            <option value="">Todos</option>
                            <option value="scheduled" @selected(request('status') === 'scheduled')>Agendado</option>
                            <option value="ongoing" @selected(request('status') === 'ongoing')>Em andamento</option>
                            <option value="completed" @selected(request('status') === 'completed')>Concluído</option>
                            <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelado</option>
                        </select>
                    </div>
                    <div class="mg-clients-filters__actions">
                        <button type="submit" class="mg-btn-primary">Aplicar</button>
                        <a href="{{ route('events.index') }}" class="mg-btn-ghost">Limpar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="mg-client-list">
        @forelse($events as $event)
            @php
                [$label, $chip] = $statusMap[$event->status] ?? [$event->status, ''];
            @endphp
            <div class="mg-client-card mg-product-card">
                <a href="{{ route('events.show', $event) }}" class="mg-client-card__main text-decoration-none">
                    <div class="mg-client-card__avatar" style="background:linear-gradient(135deg,#0f766e,#14b8a6)">
                        <i class="ri-calendar-event-line"></i>
                    </div>
                    <div class="mg-client-card__identity">
                        <div class="mg-client-card__name">{{ $event->title }}</div>
                        <div class="mg-client-card__meta">
                            <span>{{ $event->start_time->format('d/m/Y H:i') }}</span>
                            <span class="mg-client-card__sep">|</span>
                            <span>{{ $event->location ?? 'Sem local' }}</span>
                            @if($event->max_participants)
                                <span class="mg-client-card__sep">|</span>
                                <span>{{ $event->registered_count }}/{{ $event->max_participants }} participantes</span>
                            @elseif($event->registered_count)
                                <span class="mg-client-card__sep">|</span>
                                <span>{{ $event->registered_count }} participantes</span>
                            @endif
                        </div>
                        <div class="mg-client-chips">
                            <span class="mg-chip {{ $chip }}">{{ $label }}</span>
                        </div>
                    </div>
                </a>
                <div class="mg-client-card__actions mg-product-card__actions">
                    <a href="{{ route('events.edit', $event) }}" class="mg-btn-ghost mg-btn-ghost--sm" title="Editar">
                        <i class="ri-pencil-line"></i>
                    </a>
                    <button type="button" class="mg-btn-ghost mg-btn-ghost--sm mg-btn-danger-ghost" title="Excluir" onclick="deleteEvent({{ $event->id }})">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                    <a href="{{ route('events.show', $event) }}" class="mg-client-card__chevron-link">
                        <i class="ri-arrow-right-s-line mg-client-card__chevron"></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="mg-empty-state">
                <i class="ri-calendar-event-line"></i>
                <p>Nenhum evento cadastrado.</p>
                <a href="{{ route('events.create') }}" class="mg-btn-primary">Criar evento</a>
            </div>
        @endforelse
    </div>

    @if($events->hasPages())
        <div class="mg-pagination">{{ $events->withQueryString()->links() }}</div>
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
