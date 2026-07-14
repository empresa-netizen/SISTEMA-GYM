@extends('layouts.master')

@section('title', 'Feed')

@section('content')
<div class="mg-clients-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Feed</h1>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter">
                    <i class="ri-rss-line"></i>
                    {{ $summary['posts'] }} posts
                </span>
                <span class="mg-clients-counter mg-clients-counter--pending">
                    <i class="ri-error-warning-fill"></i>
                    {{ $summary['late'] }} atrasos
                </span>
                <span class="mg-clients-counter">
                    <i class="ri-message-3-line"></i>
                    {{ $summary['messages'] }} msgs
                </span>
                <span class="mg-clients-counter mg-clients-counter--delivered">
                    <i class="ri-chat-quote-line"></i>
                    {{ $summary['feedbacks'] }} feedbacks
                </span>
            </div>
        </div>
        <div class="mg-clients-toolbar__right">
            <a href="{{ route('feed.news') }}" class="mg-btn-ghost"><i class="ri-newspaper-line"></i> Notícias</a>
            <a href="{{ route('community.index') }}" class="mg-btn-ghost"><i class="ri-community-line"></i> Comunidade</a>
        </div>
    </div>

    <p class="mg-page-sub mb-0">Timeline com posts, curtidas e comentários — dados locais.</p>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="mg-panel mg-panel--compact">
        <div class="mg-panel-label mb-3">NOVO POST</div>
        <form method="POST" action="{{ route('feed.store') }}" enctype="multipart/form-data" class="mg-feed-compose">
            @csrf
            <input type="text" name="title" class="mg-field" placeholder="Título do post" required>
            <textarea name="description" class="mg-field" rows="3" placeholder="Escreva a publicação..."></textarea>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <input type="file" name="image" class="mg-field" accept="image/*" style="max-width:280px">
                <button type="submit" class="mg-btn-primary"><i class="ri-send-plane-line"></i> Publicar</button>
            </div>
        </form>
    </div>

    <div class="mg-client-list">
        @forelse($items as $item)
            @php
                $typeMap = [
                    'POST' => ['Post', 'mg-chip--success'],
                    'NEWS' => ['Notícia', 'mg-chip--info'],
                    'DELIVERY_LATE' => ['Entrega', 'mg-chip--danger'],
                    'CONVERSATION' => ['Mensagem', 'mg-chip--info'],
                    'FEEDBACK' => ['Feedback', 'mg-chip--warn'],
                ];
                [$label, $chipClass] = $typeMap[$item->type] ?? [$item->type, ''];
                $liked = in_array($item->id, $likedIds ?? [], true);
                $likesCount = $item->likes_count ?? $item->likes_count ?? 0;
                $commentsCount = $item->comments_count ?? 0;
            @endphp
            <article class="mg-feed-card">
                <div class="d-flex gap-3">
                    <div class="mg-feed-icon"><i class="ri-notification-3-line"></i></div>
                    <div class="flex-grow-1 min-width-0">
                        <div class="d-flex flex-wrap gap-2 align-items-center mb-1">
                            <span class="mg-chip {{ $chipClass }}">{{ $label }}</span>
                            <span class="text-muted small">{{ $item->created_at->format('d/m/Y H:i') }}</span>
                            @if($item->author)
                                <span class="text-muted small">por {{ $item->author->name }}</span>
                            @endif
                        </div>
                        <h6 class="mb-1">{{ $item->title }}</h6>
                        @if($item->description)
                            <p class="text-muted small mb-2">{!! nl2br(e($item->description)) !!}</p>
                        @endif
                        @if($item->image_path)
                            <img src="{{ $item->image_url }}" alt="" class="img-fluid rounded mb-2" style="max-height:280px;object-fit:cover;">
                        @endif
                        <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                            <form method="POST" action="{{ route('feed.like', $item) }}" class="m-0">
                                @csrf
                                <button type="submit" class="mg-btn-ghost {{ $liked ? 'is-active' : '' }}">
                                    <i class="{{ $liked ? 'ri-heart-fill' : 'ri-heart-line' }}"></i>
                                    {{ $likesCount }} curtida{{ $likesCount === 1 ? '' : 's' }}
                                </button>
                            </form>
                            <span class="mg-chip">{{ $commentsCount }} comentário{{ $commentsCount === 1 ? '' : 's' }}</span>
                            @if($item->member)
                                <span class="small fw-medium">{{ $item->member->name }}</span>
                            @endif
                        </div>

                        @if($item->comments->isNotEmpty())
                            <div class="mg-note-box mb-2">
                                @foreach($item->comments->take(5) as $comment)
                                    <div class="mb-2">
                                        <strong class="small">{{ $comment->user?->name ?? 'Coach' }}</strong>
                                        <span class="text-muted small">· {{ $comment->created_at?->format('d/m H:i') }}</span>
                                        <p class="small mb-0">{{ $comment->body }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <form method="POST" action="{{ route('feed.comment', $item) }}" class="d-flex gap-2">
                            @csrf
                            <input type="text" name="body" class="mg-field" placeholder="Escreva um comentário..." required>
                            <button type="submit" class="mg-btn-primary"><i class="ri-chat-1-line"></i></button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="mg-empty-state">
                <i class="ri-rss-line"></i>
                <p>Nenhum item no feed ainda.</p>
            </div>
        @endforelse
    </div>

    @if($items->hasPages())
        <div class="mg-pagination">{{ $items->links() }}</div>
    @endif
</div>
@endsection
