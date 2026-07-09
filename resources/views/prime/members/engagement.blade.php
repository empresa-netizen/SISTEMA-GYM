@extends('layouts.master')

@section('title', 'Engajamento dos Alunos')

@section('content')
<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Engajamento dos Alunos</h1>
            <p class="prime-page-sub mb-0">Ranking de alunos por XP calculado com atividades locais reais.</p>
            <div class="prime-clients-counters">
                <span class="prime-clients-counter">
                    <i class="ri-trophy-line"></i>
                    {{ $members->total() }} {{ $members->total() === 1 ? 'aluno ranqueado' : 'alunos ranqueados' }}
                </span>
            </div>
        </div>
        <div class="prime-clients-toolbar__right">
            <a href="{{ route('members.engagement', array_merge(request()->except('export', 'page'), ['export' => 1])) }}" class="prime-btn-ghost">
                <i class="ri-download-2-line"></i> Exportar lista
            </a>
        </div>
    </div>

    <div class="prime-engagement-controls">
        <div class="prime-engagement-toggle" aria-label="Alternar ranking">
            <a href="{{ route('members.engagement', array_merge(request()->except('page', 'export'), ['scope' => 'season'])) }}" class="prime-engagement-toggle__btn {{ $filters['scope'] === 'season' ? 'is-active' : '' }}">
                <i class="ri-calendar-event-line"></i> Temporada Atual
            </a>
            <a href="{{ route('members.engagement', array_merge(request()->except('page', 'export'), ['scope' => 'history'])) }}" class="prime-engagement-toggle__btn {{ $filters['scope'] === 'history' ? 'is-active' : '' }}">
                <i class="ri-history-line"></i> Histórico Geral
            </a>
        </div>

        <form method="GET" action="{{ route('members.engagement') }}" class="prime-engagement-search">
            <input type="hidden" name="scope" value="{{ $filters['scope'] }}">
            <label for="engagementSearch" class="prime-field-label">Buscar</label>
            <div class="prime-field-with-icon">
                <i class="ri-search-line"></i>
                <input id="engagementSearch" type="search" name="q" value="{{ $filters['q'] }}" class="prime-field" placeholder="Nome, email ou WhatsApp">
            </div>
            <button type="submit" class="prime-btn-primary"><i class="ri-search-line"></i> Buscar</button>
            @if($filters['q'] !== '')
                <a href="{{ route('members.engagement', ['scope' => $filters['scope']]) }}" class="prime-btn-ghost"><i class="ri-close-line"></i> Limpar</a>
            @endif
        </form>
    </div>

    <div class="prime-engagement-banner">
        <i class="ri-information-line"></i>
        <div>
            <strong>Ranking da temporada</strong>
            <p class="mb-0">
                @if($filters['scope'] === 'season')
                    Considera atividades de {{ $seasonLabel }}.
                @else
                    Considera todo o histórico local disponível.
                @endif
                XP = treinos concluídos, exercícios marcados, diários, feedbacks e fotos de progresso.
            </p>
        </div>
    </div>

    <div class="prime-client-list prime-engagement-list">
        @forelse($members as $member)
            @php
                $initials = collect(explode(' ', $member->name))->filter()->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
                $profileUrl = route('members.show', [$member, 'tab' => 'progress']);
                $waPhone = $member->phone ? preg_replace('/\D+/', '', $member->phone) : null;
                $rankClass = match ($member->engagement_rank) {
                    1 => 'prime-engagement-card--gold',
                    2 => 'prime-engagement-card--silver',
                    3 => 'prime-engagement-card--bronze',
                    default => '',
                };
            @endphp
            <div class="prime-client-card prime-engagement-card {{ $rankClass }}">
                <div class="prime-engagement-rank">#{{ $member->engagement_rank }}</div>
                <div class="prime-client-card__main">
                    <a href="{{ $profileUrl }}" class="prime-client-card__avatar-link">
                        @if($member->photo)
                            <img src="{{ asset('storage/'.$member->photo) }}" alt="" class="prime-client-card__avatar-img">
                        @else
                            <div class="prime-client-card__avatar">{{ strtoupper($initials) }}</div>
                        @endif
                    </a>
                    <div class="prime-client-card__identity">
                        <a href="{{ $profileUrl }}" class="prime-client-card__name">{{ $member->name }}</a>
                        <div class="prime-client-card__meta">
                            <span>{{ $member->email }}</span>
                            @if($member->phone)<span class="prime-client-card__sep">|</span><span>{{ $member->phone }}</span>@endif
                        </div>
                        <div class="prime-client-chips">
                            <span class="prime-chip prime-chip--info"><i class="ri-dumbbell-line"></i> {{ $member->completed_workouts_count }} treinos</span>
                            <span class="prime-chip"><i class="ri-checkbox-circle-line"></i> {{ $member->completed_activities_count }} exercícios</span>
                            <span class="prime-chip"><i class="ri-book-2-line"></i> {{ $member->logbooks_count }} diários</span>
                            <span class="prime-chip"><i class="ri-chat-quote-line"></i> {{ $member->feedbacks_count }} feedbacks</span>
                            <span class="prime-chip"><i class="ri-image-line"></i> {{ $member->photos_count }} fotos</span>
                        </div>
                    </div>
                </div>
                <div class="prime-engagement-xp">
                    <span>{{ number_format($member->engagement_xp, 0, ',', '.') }}</span>
                    <strong>XP</strong>
                </div>
                <div class="prime-client-card__actions">
                    <a href="{{ $profileUrl }}" class="prime-btn-ghost prime-btn-ghost--sm">
                        <i class="ri-user-search-line"></i> Detalhes
                    </a>
                    @if($waPhone)
                        <a href="https://wa.me/{{ $waPhone }}" target="_blank" rel="noopener" class="prime-icon-btn prime-icon-btn--whatsapp" title="WhatsApp">
                            <i class="ri-whatsapp-line"></i>
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="prime-empty-state">
                <i class="ri-line-chart-line"></i>
                <p>Nenhum aluno ativo encontrado para este ranking.</p>
                @if($filters['q'] !== '')
                    <a href="{{ route('members.engagement', ['scope' => $filters['scope']]) }}" class="prime-btn-ghost">Limpar busca</a>
                @endif
            </div>
        @endforelse
    </div>

    @if($members->hasPages())
        <div class="prime-pagination">{{ $members->links() }}</div>
    @endif
</div>
@endsection
