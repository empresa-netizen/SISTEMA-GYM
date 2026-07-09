@extends('layouts.master')

@section('title', $group->name)

@section('content')
<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">{{ $group->name }}</h1>
            <div class="prime-clients-counters">
                <span class="prime-clients-counter">
                    <i class="ri-chat-3-line"></i>
                    {{ $group->posts->count() }} posts
                </span>
            </div>
        </div>
        <div class="prime-clients-toolbar__right">
            <a href="{{ route('community.index') }}" class="prime-btn-ghost">
                <i class="ri-arrow-left-line"></i> Voltar
            </a>
        </div>
    </div>

    @if($group->description)
        <p class="prime-page-sub mb-0">{{ $group->description }}</p>
    @endif

    <div class="prime-panel prime-panel--compact">
        <div class="prime-panel-label mb-3">PUBLICAR NO GRUPO</div>
        <form method="POST" action="{{ route('community.posts.store', $group) }}" class="prime-feed-compose">
            @csrf
            <input type="text" name="content" class="prime-field" placeholder="Publicar no grupo..." required>
            <button type="submit" class="prime-btn-primary">Publicar</button>
        </form>
    </div>

    <div class="prime-client-list">
        @forelse($group->posts as $post)
            <div class="prime-feed-card">
                <div class="d-flex justify-content-between mb-2 gap-2">
                    <strong>{{ $post->member?->name ?? 'Coach' }}</strong>
                    <span class="text-muted small">{{ $post->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <p class="mb-2">{{ $post->content }}</p>
                <span class="prime-chip"><i class="ri-heart-line"></i> {{ $post->likes_count }}</span>
            </div>
        @empty
            <div class="prime-empty-state">
                <i class="ri-community-line"></i>
                <p>Nenhum post neste grupo. Seja o primeiro a publicar.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
