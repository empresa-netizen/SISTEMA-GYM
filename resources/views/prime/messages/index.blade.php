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

<div class="prime-clients-page prime-messages-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Mensagens</h1>
            <p class="prime-page-sub mb-0">Conecte-se com os seus alunos</p>
            <div class="prime-clients-counters">
                <span class="prime-clients-counter">
                    <i class="ri-message-3-line"></i>
                    {{ $conversations->count() }} conversas
                </span>
                @if($unread > 0)
                    <span class="prime-clients-counter prime-clients-counter--pending">
                        <i class="ri-notification-3-fill"></i>
                        {{ $unread }} não lidas
                    </span>
                @endif
            </div>
        </div>
        <div class="prime-clients-toolbar__right">
            <a href="{{ route('members.index') }}" class="prime-btn-ghost">
                <i class="ri-group-line"></i> Clientes ativos
            </a>
        </div>
    </div>

    <div class="prime-clients-filters prime-messages-filters">
        <button type="button" class="prime-btn-ghost prime-filters-toggle {{ $filtersOpen ? 'is-open' : '' }}" data-bs-toggle="collapse" data-bs-target="#primeMessageFilters" aria-expanded="{{ $filtersOpen ? 'true' : 'false' }}">
            <i class="ri-filter-3-line"></i> Filtros
            <i class="ri-arrow-down-s-line prime-filters-chevron"></i>
        </button>
        <div class="collapse {{ $filtersOpen ? 'show' : '' }}" id="primeMessageFilters">
            <form method="GET" action="{{ route('messages.index') }}" class="prime-clients-filters__form">
                <div class="prime-clients-filters__grid">
                    <div>
                        <label class="prime-field-label">Buscar aluno</label>
                        <input type="search" name="q" value="{{ request('q') }}" class="prime-field" placeholder="Nome, e-mail ou ID do aluno">
                    </div>
                    <div>
                        <label class="prime-field-label">Produto</label>
                        <select name="product" class="prime-field">
                            <option value="">Todos os produtos</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" @selected((string) request('product') === (string) $plan->id)>{{ $plan->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="prime-field-label">Status</label>
                        <select name="status" class="prime-field">
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="prime-clients-filters__actions">
                        <button type="submit" class="prime-btn-primary"><i class="ri-search-line"></i> Buscar</button>
                        <a href="{{ route('messages.index') }}" class="prime-btn-ghost">Limpar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="prime-chat-layout">
        <aside class="prime-chat-list">
            @forelse($conversations as $conv)
                @php
                    $studentName = $conv->member?->name ?? 'Aluno removido';
                    $initials = collect(explode(' ', $studentName))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
                    $planName = $conv->member?->membershipPlan?->name ?? 'Sem produto';
                    $lastMessage = $conv->last_message ?: 'Sem mensagens ainda';
                    $conversationUrl = route('messages.index', array_merge(request()->only(['q', 'product', 'status']), ['conversation' => $conv->id]));
                @endphp
                <a href="{{ $conversationUrl }}"
                   class="prime-chat-item @if($active && $active->id === $conv->id) is-active @endif @if($conv->unread_by_coach) is-unread @endif">
                    <div class="prime-chat-item__avatar">{{ strtoupper($initials ?: '?') }}</div>
                    <div class="prime-chat-item__body">
                        <div class="prime-chat-item__top">
                            <strong>{{ $studentName }}</strong>
                            <span>{{ $conv->last_message_at?->diffForHumans() ?? 'Agora' }}</span>
                        </div>
                        <div class="prime-chat-item__badges">
                            <span class="prime-chip prime-chip--info">{{ $planName }}</span>
                            @if($conv->unread_by_coach)
                                <span class="prime-chip prime-chip--warn">Não lida</span>
                            @elseif($conv->member && $conv->member->status !== 'active')
                                <span class="prime-chip">Encerrada</span>
                            @endif
                        </div>
                        <p>{{ $lastMessage }}</p>
                    </div>
                    <i class="ri-arrow-right-s-line prime-chat-item__chevron"></i>
                </a>
            @empty
                <div class="prime-empty-state prime-empty-state--compact" style="margin:0.75rem;border:0;background:transparent">
                    <i class="ri-message-3-line"></i>
                    <p>Nenhuma conversa encontrada.</p>
                </div>
            @endforelse
        </aside>

        <section class="prime-chat-main">
            @if($active)
                @php
                    $activeName = $active->member?->name ?? 'Aluno removido';
                    $activeInitials = collect(explode(' ', $activeName))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
                    $activePlan = $active->member?->membershipPlan?->name ?? 'Sem produto';
                @endphp
                <div class="prime-chat-header">
                    <div class="d-flex align-items-center gap-2">
                        <div class="prime-chat-item__avatar">{{ strtoupper($activeInitials ?: '?') }}</div>
                        <div>
                            <strong>{{ $activeName }}</strong>
                            <div class="prime-chat-header__meta">
                                <span>{{ $active->member?->email ?? 'E-mail não informado' }}</span>
                                <span class="prime-client-card__sep">|</span>
                                <span>{{ $activePlan }}</span>
                            </div>
                        </div>
                    </div>
                    @if($active->member)
                        <a href="{{ route('members.show', $active->member) }}" class="prime-btn-ghost prime-btn-ghost--sm">Ver ficha</a>
                    @endif
                </div>
                <div class="prime-chat-messages" id="chatMessages" @if($active) data-conversation-id="{{ $active->id }}" @endif>
                    @forelse($active->messages as $msg)
                        <div class="prime-chat-bubble {{ $msg->sender_type === 'coach' ? 'is-sent' : 'is-received' }}" data-message-id="{{ $msg->id }}">
                            <p class="mb-0">{{ $msg->content }}</p>
                            <span class="prime-chat-time">{{ $msg->created_at->format('d/m H:i') }}</span>
                        </div>
                    @empty
                        <div class="prime-empty-state prime-empty-state--compact m-auto" style="border:0;background:transparent">
                            <i class="ri-chat-1-line"></i>
                            <p>Nenhuma mensagem. Inicie a conversa abaixo.</p>
                        </div>
                    @endforelse
                </div>
                <form method="POST" action="{{ route('messages.store', $active) }}" class="prime-chat-compose">
                    @csrf
                    <input type="text" name="content" class="prime-field" placeholder="Digite sua mensagem..." required autocomplete="off">
                    <button type="submit" class="prime-btn-primary" aria-label="Enviar">
                        <i class="ri-send-plane-2-line"></i>
                    </button>
                </form>
            @else
                <div class="prime-empty-state m-auto" style="border:0;background:transparent;width:100%">
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
