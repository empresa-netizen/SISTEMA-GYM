@extends('layouts.master')

@section('title', 'Notificações')

@section('content')
<div class="mg-clients-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Notificações</h1>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter">
                    <i class="ri-notification-3-line"></i>
                    {{ auth()->user()->unreadNotifications->count() }} não lidas
                </span>
            </div>
        </div>
        <div class="mg-clients-toolbar__right">
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button class="mg-btn-ghost" type="submit"><i class="ri-check-double-line"></i> Marcar todas como lidas</button>
            </form>
        </div>
    </div>

    <div class="mg-client-list">
        @forelse($notifications as $notification)
            @php $data = $notification->data; @endphp
            <article class="mg-feed-card {{ $notification->read_at ? '' : 'is-unread' }}">
                <div class="d-flex gap-3 align-items-start">
                    <div class="mg-feed-icon"><i class="{{ $data['icon'] ?? 'ri-notification-3-line' }}"></i></div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between gap-2">
                            <h6 class="mb-1">{{ $data['title'] ?? 'Notificação' }}</h6>
                            <span class="text-muted small">{{ $notification->created_at?->diffForHumans() }}</span>
                        </div>
                        <p class="text-muted small mb-2">{{ $data['body'] ?? '' }}</p>
                        <div class="d-flex gap-2">
                            @if(!empty($data['url']))
                                <a href="{{ $data['url'] }}" class="mg-btn-ghost">Abrir</a>
                            @endif
                            @unless($notification->read_at)
                                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                    @csrf
                                    <button class="mg-btn-primary" type="submit">Marcar como lida</button>
                                </form>
                            @endunless
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="mg-empty-state">
                <i class="ri-notification-off-line"></i>
                <p>Nenhuma notificação por aqui.</p>
            </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <div class="mg-pagination">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection
