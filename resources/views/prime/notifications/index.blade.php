@extends('layouts.master')

@section('title', 'Notificações')

@section('content')
<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Notificações</h1>
            <div class="prime-clients-counters">
                <span class="prime-clients-counter">
                    <i class="ri-notification-3-line"></i>
                    {{ auth()->user()->unreadNotifications->count() }} não lidas
                </span>
            </div>
        </div>
        <div class="prime-clients-toolbar__right">
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button class="prime-btn-ghost" type="submit"><i class="ri-check-double-line"></i> Marcar todas como lidas</button>
            </form>
        </div>
    </div>

    <div class="prime-client-list">
        @forelse($notifications as $notification)
            @php $data = $notification->data; @endphp
            <article class="prime-feed-card {{ $notification->read_at ? '' : 'is-unread' }}">
                <div class="d-flex gap-3 align-items-start">
                    <div class="prime-feed-icon"><i class="{{ $data['icon'] ?? 'ri-notification-3-line' }}"></i></div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between gap-2">
                            <h6 class="mb-1">{{ $data['title'] ?? 'Notificação' }}</h6>
                            <span class="text-muted small">{{ $notification->created_at?->diffForHumans() }}</span>
                        </div>
                        <p class="text-muted small mb-2">{{ $data['body'] ?? '' }}</p>
                        <div class="d-flex gap-2">
                            @if(!empty($data['url']))
                                <a href="{{ $data['url'] }}" class="prime-btn-ghost">Abrir</a>
                            @endif
                            @unless($notification->read_at)
                                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                    @csrf
                                    <button class="prime-btn-primary" type="submit">Marcar como lida</button>
                                </form>
                            @endunless
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="prime-empty-state">
                <i class="ri-notification-off-line"></i>
                <p>Nenhuma notificação por aqui.</p>
            </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <div class="prime-pagination">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection
