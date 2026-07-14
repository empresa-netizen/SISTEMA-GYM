@extends('layouts.master')

@section('title', $group->name)

@section('content')
<div class="mg-clients-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">{{ $group->name }}</h1>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter">
                    <i class="ri-chat-3-line"></i>
                    {{ $group->posts->count() }} posts
                </span>
            </div>
        </div>
        <div class="mg-clients-toolbar__right">
            <a href="{{ route('community.index') }}" class="mg-btn-ghost">
                <i class="ri-arrow-left-line"></i> Voltar
            </a>
        </div>
    </div>

    @if($group->description)
        <p class="mg-page-sub mb-0">{{ $group->description }}</p>
    @endif

    <div class="mg-panel mg-panel--compact">
        <div class="mg-panel-label mb-3">PUBLICAR NO GRUPO</div>
        <form method="POST" action="{{ route('community.posts.store', $group) }}" class="mg-feed-compose">
            @csrf
            <input type="text" name="content" class="mg-field" placeholder="Publicar no grupo..." required>
            <button type="submit" class="mg-btn-primary">Publicar</button>
        </form>
    </div>

    <div class="mg-client-list">
        @forelse($group->posts as $post)
            <div class="mg-feed-card">
                <div class="d-flex justify-content-between mb-2 gap-2">
                    <strong>{{ $post->member?->name ?? 'Coach' }}</strong>
                    <span class="text-muted small">{{ $post->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <p class="mb-2">{{ $post->content }}</p>
                <span class="mg-chip"><i class="ri-heart-line"></i> {{ $post->likes_count }}</span>
            </div>
        @empty
            <div class="mg-empty-state">
                <i class="ri-community-line"></i>
                <p>Nenhum post neste grupo. Publique antes de todos.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
