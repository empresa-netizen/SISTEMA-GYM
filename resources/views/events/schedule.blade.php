@extends('layouts.master')

@section('title', 'Agenda')

@section('content')
<div class="prime-clients-page prime-agenda-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Agenda</h1>
            <div class="prime-clients-counters">
                <span class="prime-clients-counter">
                    <i class="ri-calendar-2-line"></i>
                    Central de agendamentos
                </span>
            </div>
        </div>
        <div class="prime-clients-toolbar__right">
            <a href="{{ route('events.index') }}" class="prime-btn-ghost">
                <i class="ri-list-check-2"></i> Lista
            </a>
            <a href="{{ route('events.create') }}" class="prime-btn-primary">
                <i class="ri-add-line"></i> Novo evento
            </a>
        </div>
    </div>

    <div class="prime-agenda-legend-bar">
        <div class="prime-agenda-legend-bar__left">
            <span class="prime-panel-label mb-0">Tipos</span>
            <div class="prime-agenda-legends">
                <span class="prime-calendar-legend prime-calendar-legend--evaluation">Avaliações</span>
                <span class="prime-calendar-legend prime-calendar-legend--consulting">Consultas</span>
                <span class="prime-calendar-legend prime-calendar-legend--pending">Aguardando confirmação</span>
            </div>
        </div>
        <p class="prime-agenda-hint mb-0">Consultas, avaliações e retornos · clique no evento para abrir</p>
    </div>

    <div class="prime-panel prime-panel--compact prime-panel--calendar prime-calendar-panel">
        <div id="primeCalendar" class="prime-calendar-surface"></div>
    </div>
</div>
@endsection

@section('script')
<script src="{{ URL::asset('build/libs/fullcalendar/index.global.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('primeCalendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'pt-br',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        buttonText: { today: 'Hoje', month: 'Mês', week: 'Semana', list: 'Lista' },
        height: 'auto',
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
        dayMaxEvents: 3,
        nowIndicator: true,
        events: '{{ route('events.feed') }}',
        eventClick: function(info) {
            if (info.event.url) {
                info.jsEvent.preventDefault();
                window.location.href = info.event.url;
            }
        }
    });
    calendar.render();
});
</script>
@endsection
