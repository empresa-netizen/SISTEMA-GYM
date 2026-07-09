@extends('layouts.master')

@section('title', 'Seu Feed de Notícias')

@section('content')
<div class="prime-news-page">
    <div class="prime-news-shell">
        <main class="prime-news-main">
            <div class="prime-news-header">
                <div>
                    <h1 class="prime-page-title mb-0">Seu Feed de Notícias</h1>
                    <p class="prime-page-sub mb-0">Posts locais publicados para manter alunos e equipe alinhados.</p>
                </div>
                <div class="prime-news-actions">
                    <a href="{{ route('notice-boards.index') }}" class="prime-btn-ghost">
                        <i class="ri-layout-top-2-line"></i> Gerenciar Banners
                    </a>
                    <a href="#new-feed-post" class="prime-btn-primary">
                        <i class="ri-add-line"></i> Criar novo post
                    </a>
                </div>
            </div>

            <section id="new-feed-post" class="prime-news-composer">
                <form method="POST" action="{{ route('feed.store') }}" enctype="multipart/form-data" class="prime-news-composer__form">
                    @csrf
                    <div class="prime-news-avatar prime-news-avatar--coach">
                        <i class="ri-user-star-line"></i>
                    </div>
                    <div class="prime-news-composer__body">
                        <input type="text" name="title" class="prime-field prime-news-composer__input" placeholder="O que você está pensando?" required>
                        <textarea name="description" class="prime-field prime-news-composer__textarea" rows="2" placeholder="Compartilhe uma novidade, orientação ou recado para seus alunos."></textarea>
                        <div class="prime-news-composer__footer">
                            <input type="file" name="image" class="prime-field" accept="image/*" style="max-width:220px">
                            <button type="submit" class="prime-btn-primary">
                                <i class="ri-send-plane-line"></i> Publicar
                            </button>
                        </div>
                    </div>
                </form>
            </section>

            <div class="prime-news-list">
                @forelse($items as $item)
                    @php
                        $author = $item->member?->name ?: 'Coach';
                        $initials = collect(explode(' ', trim($author)))
                            ->filter()
                            ->take(2)
                            ->map(fn ($part) => mb_substr($part, 0, 1))
                            ->implode('');
                        $avatar = $item->member?->photo ? asset('storage/'.$item->member->photo) : null;
                        $likes = $item->likes_count ?? 0;
                        $comments = $item->comments_count ?? 0;
                        $liked = in_array($item->id, $likedIds ?? [], true);
                    @endphp
                    <article class="prime-news-card">
                        <header class="prime-news-card__header">
                            @if($avatar)
                                <img src="{{ $avatar }}" alt="" class="prime-news-avatar">
                            @else
                                <div class="prime-news-avatar">{{ strtoupper($initials ?: 'C') }}</div>
                            @endif
                            <div class="prime-news-card__identity">
                                <strong>{{ $author }}</strong>
                                <span>{{ $item->created_at?->diffForHumans() }} · {{ $item->created_at?->format('d/m/Y H:i') }}</span>
                            </div>
                            <span class="prime-chip prime-chip--info">{{ $item->type === 'NEWS' ? 'Notícia' : 'Post' }}</span>
                        </header>

                        <div class="prime-news-card__content">
                            <h2>{{ $item->title }}</h2>
                            @if($item->description)
                                <p>{{ $item->description }}</p>
                            @endif
                        </div>

                        @if($item->image_path)
                            <div class="prime-news-card__media">
                                <img src="{{ $item->image_url }}" alt="" class="img-fluid rounded">
                            </div>
                        @else
                            <div class="prime-news-card__media" aria-label="Mídia do post">
                                <i class="ri-image-line"></i>
                                <span>{{ $item->meta ?: 'Post local sem mídia anexada' }}</span>
                            </div>
                        @endif

                        <footer class="prime-news-card__footer">
                            <form method="POST" action="{{ route('feed.like', $item) }}" class="m-0 d-inline">
                                @csrf
                                <button type="submit" class="btn btn-link p-0 text-decoration-none">
                                    <i class="{{ $liked ? 'ri-heart-3-fill' : 'ri-heart-3-line' }}"></i> {{ $likes }} curtidas
                                </button>
                            </form>
                            <span><i class="ri-chat-3-line"></i> {{ $comments }} comentários</span>
                        </footer>
                    </article>
                @empty
                    <div class="prime-empty-state prime-news-empty">
                        <i class="ri-newspaper-line"></i>
                        <p>Nenhuma novidade publicada ainda.</p>
                        <a href="#new-feed-post" class="prime-btn-primary">
                            <i class="ri-add-line"></i> Criar primeiro post
                        </a>
                    </div>
                @endforelse
            </div>

            @if($items->hasPages())
                <div class="prime-pagination">{{ $items->links() }}</div>
            @endif
        </main>

        <aside class="prime-news-guide">
            <div class="prime-panel prime-panel--compact">
                <div class="prime-panel-label">Guia de Uso</div>
                <h2 class="prime-news-guide__title">Publique comunicados rápidos</h2>
                <p class="prime-page-sub mb-3">Use o feed para avisos, lançamentos e orientações gerais. Os posts ficam salvos em dados locais de Feed.</p>
                <div class="prime-news-guide__stats">
                    <span><strong>{{ $summary['posts'] }}</strong> posts</span>
                    <span><strong>{{ $summary['coach_posts'] }}</strong> do coach</span>
                    <span><strong>{{ $summary['member_posts'] }}</strong> com aluno</span>
                </div>
                <div class="prime-news-guide__links">
                    <a href="{{ route('feed.index') }}"><i class="ri-rss-line"></i> Feed operacional</a>
                    <a href="{{ route('community.index') }}"><i class="ri-community-line"></i> Comunidade</a>
                    <a href="{{ route('patch-notes') }}"><i class="ri-file-list-3-line"></i> Patch notes</a>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
