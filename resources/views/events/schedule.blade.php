@extends('layouts.master')

@section('title', 'Agenda')

@section('content')
<div class="mg-clients-page mg-agenda-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Agenda</h1>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter">
                    <i class="ri-calendar-2-line"></i>
                    Central de agendamentos
                </span>
            </div>
        </div>
        <div class="mg-clients-toolbar__right">
            <a href="{{ route('events.index') }}" class="mg-btn-ghost">
                <i class="ri-list-check-2"></i> Lista
            </a>
            <a href="{{ route('events.create') }}" class="mg-btn-primary">
                <i class="ri-add-line"></i> Novo evento
            </a>
        </div>
    </div>

    <div class="mg-agenda-legend-bar">
        <div class="mg-agenda-legend-bar__left">
            <span class="mg-panel-label mb-0">Tipos</span>
            <div class="mg-agenda-legends">
                <span class="mg-calendar-legend mg-calendar-legend--evaluation">Avaliações</span>
                <span class="mg-calendar-legend mg-calendar-legend--consulting">Consultas</span>
                <span class="mg-calendar-legend mg-calendar-legend--pending">Aguardando confirmação</span>
            </div>
        </div>
        <p class="mg-agenda-hint mb-0">Consultas, avaliações e retornos · clique no evento para abrir</p>
    </div>

    <div class="mg-panel mg-panel--compact mg-panel--calendar mg-calendar-panel">
        <div id="mgCalendar" class="mg-calendar-surface"></div>
    </div>
</div>
@endsection

@section('script')
<script src="{{ URL::asset('build/libs/fullcalendar/index.global.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('mgCalendar');
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
