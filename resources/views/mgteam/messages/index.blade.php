@extends('layouts.master')

@section('title', 'Mensagens')

@section('content')
@php
    $filtersOpen = request()->hasAny(['q', 'product', 'status']);
    $statusOptions = [
        'all' => 'Todas',
        'unread' => 'Não lidas',
        'read' => 'Lidas',
        'closed' => 'Encerradas',
    ];
    $unread = $conversations->where('unread_by_coach', true)->count();
@endphp

<div class="mg-clients-page mg-messages-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Mensagens</h1>
            <p class="mg-page-sub mb-0">Conecte-se com os seus alunos</p>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter">
                    <i class="ri-message-3-line"></i>
                    {{ $conversations->count() }} conversas
                </span>
                @if($unread > 0)
                    <span class="mg-clients-counter mg-clients-counter--pending">
                        <i class="ri-notification-3-fill"></i>
                        {{ $unread }} não lidas
                    </span>
                @endif
            </div>
        </div>
        <div class="mg-clients-toolbar__right">
            <a href="{{ route('members.index') }}" class="mg-btn-ghost">
                <i class="ri-group-line"></i> Clientes ativos
            </a>
        </div>
    </div>

    <div class="mg-clients-filters mg-messages-filters">
        <button type="button" class="mg-btn-ghost mg-filters-toggle {{ $filtersOpen ? 'is-open' : '' }}" data-bs-toggle="collapse" data-bs-target="#mgMessageFilters" aria-expanded="{{ $filtersOpen ? 'true' : 'false' }}">
            <i class="ri-filter-3-line"></i> Filtros
            <i class="ri-arrow-down-s-line mg-filters-chevron"></i>
        </button>
        <div class="collapse {{ $filtersOpen ? 'show' : '' }}" id="mgMessageFilters">
            <form method="GET" action="{{ route('messages.index') }}" class="mg-clients-filters__form">
                <div class="mg-clients-filters__grid">
                    <div>
                        <label class="mg-field-label">Buscar aluno</label>
                        <input type="search" name="q" value="{{ request('q') }}" class="mg-field" placeholder="Nome, e-mail ou ID do aluno">
                    </div>
                    <div>
                        <label class="mg-field-label">Produto</label>
                        <select name="product" class="mg-field">
                            <option value="">Todos os produtos</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" @selected((string) request('product') === (string) $plan->id)>{{ $plan->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mg-field-label">Status</label>
                        <select name="status" class="mg-field">
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mg-clients-filters__actions">
                        <button type="submit" class="mg-btn-primary"><i class="ri-search-line"></i> Buscar</button>
                        <a href="{{ route('messages.index') }}" class="mg-btn-ghost">Limpar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="mg-chat-layout">
        <aside class="mg-chat-list">
            @forelse($conversations as $conv)
                @php
                    $studentName = $conv->member?->name ?? 'Aluno removido';
                    $initials = collect(explode(' ', $studentName))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
                    $planName = $conv->member?->membershipPlan?->name ?? 'Sem produto';
                    $lastMessage = $conv->last_message ?: 'Sem mensagens ainda';
                    $conversationUrl = route('messages.index', array_merge(request()->only(['q', 'product', 'status']), ['conversation' => $conv->id]));
                @endphp
                <a href="{{ $conversationUrl }}"
                   class="mg-chat-item @if($active && $active->id === $conv->id) is-active @endif @if($conv->unread_by_coach) is-unread @endif">
                    <div class="mg-chat-item__avatar">{{ strtoupper($initials ?: '?') }}</div>
                    <div class="mg-chat-item__body">
                        <div class="mg-chat-item__top">
                            <strong>{{ $studentName }}</strong>
                            <span>{{ $conv->last_message_at?->diffForHumans() ?? 'Agora' }}</span>
                        </div>
                        <div class="mg-chat-item__badges">
                            <span class="mg-chip mg-chip--info">{{ $planName }}</span>
                            @if($conv->unread_by_coach)
                                <span class="mg-chip mg-chip--warn">Não lida</span>
                            @elseif($conv->member && $conv->member->status !== 'active')
                                <span class="mg-chip">Encerrada</span>
                            @endif
                        </div>
                        <p>{{ $lastMessage }}</p>
                    </div>
                    <i class="ri-arrow-right-s-line mg-chat-item__chevron"></i>
                </a>
            @empty
                <div class="mg-empty-state mg-empty-state--compact" style="margin:0.75rem;border:0;background:transparent">
                    <i class="ri-message-3-line"></i>
                    <p>Nenhuma conversa encontrada.</p>
                </div>
            @endforelse
        </aside>

        <section class="mg-chat-main">
            @if($active)
                @php
                    $activeName = $active->member?->name ?? 'Aluno removido';
                    $activeInitials = collect(explode(' ', $activeName))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
                    $activePlan = $active->member?->membershipPlan?->name ?? 'Sem produto';
                @endphp
                <div class="mg-chat-header">
                    <div class="d-flex align-items-center gap-2">
                        <div class="mg-chat-item__avatar">{{ strtoupper($activeInitials ?: '?') }}</div>
                        <div>
                            <strong>{{ $activeName }}</strong>
                            <div class="mg-chat-header__meta">
                                <span>{{ $active->member?->email ?? 'E-mail não informado' }}</span>
                                <span class="mg-client-card__sep">|</span>
                                <span>{{ $activePlan }}</span>
                            </div>
                        </div>
                    </div>
                    @if($active->member)
                        <a href="{{ route('members.show', $active->member) }}" class="mg-btn-ghost mg-btn-ghost--sm">Ver ficha</a>
                    @endif
                </div>
                <div class="mg-chat-messages" id="chatMessages" @if($active) data-conversation-id="{{ $active->id }}" @endif>
                    @forelse($active->messages as $msg)
                        <div class="mg-chat-bubble {{ $msg->sender_type === 'coach' ? 'is-sent' : 'is-received' }}" data-message-id="{{ $msg->id }}">
                            <p class="mb-0">{{ $msg->content }}</p>
                            <span class="mg-chat-time">{{ $msg->created_at->format('d/m H:i') }}</span>
                        </div>
                    @empty
                        <div class="mg-empty-state mg-empty-state--compact m-auto" style="border:0;background:transparent">
                            <i class="ri-chat-1-line"></i>
                            <p>Nenhuma mensagem. Inicie a conversa abaixo.</p>
                        </div>
                    @endforelse
                </div>
                <form method="POST" action="{{ route('messages.store', $active) }}" class="mg-chat-compose">
                    @csrf
                    <input type="text" name="content" class="mg-field" placeholder="Digite sua mensagem..." required autocomplete="off">
                    <button type="submit" class="mg-btn-primary" aria-label="Enviar">
                        <i class="ri-send-plane-2-line"></i>
                    </button>
                </form>
            @else
                <div class="mg-empty-state m-auto" style="border:0;background:transparent;width:100%">
                    <i class="ri-message-3-line"></i>
                    <p>Selecione uma conversa para começar</p>
                </div>
            @endif
        </section>
    </div>
</div>
@endsection

@section('script')
@if($active)
    @vite(['resources/js/chat-realtime.js'])
@endif
<script>
document.addEventListener('DOMContentLoaded', function() {
    const el = document.getElementById('chatMessages');
    if (el) el.scrollTop = el.scrollHeight;
});
</script>
@endsection
