@extends('layouts.master')

@section('title', 'Seu Feed de Notícias')

@section('content')
<div class="mg-news-page">
    <div class="mg-news-shell">
        <main class="mg-news-main">
            <div class="mg-news-header">
                <div>
                    <h1 class="mg-page-title mb-0">Seu Feed de Notícias</h1>
                    <p class="mg-page-sub mb-0">Posts locais publicados para manter alunos e equipe alinhados.</p>
                </div>
                <div class="mg-news-actions">
                    <a href="{{ route('notice-boards.index') }}" class="mg-btn-ghost">
                        <i class="ri-layout-top-2-line"></i> Gerenciar Banners
                    </a>
                    <a href="#new-feed-post" class="mg-btn-primary">
                        <i class="ri-add-line"></i> Criar novo post
                    </a>
                </div>
            </div>

            <section id="new-feed-post" class="mg-news-composer">
                <form method="POST" action="{{ route('feed.store') }}" enctype="multipart/form-data" class="mg-news-composer__form">
                    @csrf
                    <div class="mg-news-avatar mg-news-avatar--coach">
                        <i class="ri-user-star-line"></i>
                    </div>
                    <div class="mg-news-composer__body">
                        <input type="text" name="title" class="mg-field mg-news-composer__input" placeholder="O que você está pensando?" required>
                        <textarea name="description" class="mg-field mg-news-composer__textarea" rows="2" placeholder="Compartilhe uma novidade, orientação ou recado para seus alunos."></textarea>
                        <div class="mg-news-composer__footer">
                            <input type="file" name="image" class="mg-field" accept="image/*" style="max-width:220px">
                            <button type="submit" class="mg-btn-primary">
                                <i class="ri-send-plane-line"></i> Publicar
                            </button>
                        </div>
                    </div>
                </form>
            </section>

            <div class="mg-news-list">
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
                    <article class="mg-news-card">
                        <header class="mg-news-card__header">
                            @if($avatar)
                                <img src="{{ $avatar }}" alt="" class="mg-news-avatar">
                            @else
                                <div class="mg-news-avatar">{{ strtoupper($initials ?: 'C') }}</div>
                            @endif
                            <div class="mg-news-card__identity">
                                <strong>{{ $author }}</strong>
                                <span>{{ $item->created_at?->diffForHumans() }} · {{ $item->created_at?->format('d/m/Y H:i') }}</span>
                            </div>
                            <span class="mg-chip mg-chip--info">{{ $item->type === 'NEWS' ? 'Notícia' : 'Post' }}</span>
                        </header>

                        <div class="mg-news-card__content">
                            <h2>{{ $item->title }}</h2>
                            @if($item->description)
                                <p>{{ $item->description }}</p>
                            @endif
                        </div>

                        @if($item->image_path)
                            <div class="mg-news-card__media">
                                <img src="{{ $item->image_url }}" alt="" class="img-fluid rounded">
                            </div>
                        @else
                            <div class="mg-news-card__media" aria-label="Mídia do post">
                                <i class="ri-image-line"></i>
                                <span>{{ $item->meta ?: 'Post local sem mídia anexada' }}</span>
                            </div>
                        @endif

                        <footer class="mg-news-card__footer">
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
                    <div class="mg-empty-state mg-news-empty">
                        <i class="ri-newspaper-line"></i>
                        <p>Nenhuma novidade publicada ainda.</p>
                        <a href="#new-feed-post" class="mg-btn-primary">
                            <i class="ri-add-line"></i> Criar post inicial
                        </a>
                    </div>
                @endforelse
            </div>

            @if($items->hasPages())
                <div class="mg-pagination">{{ $items->links() }}</div>
            @endif
        </main>

        <aside class="mg-news-guide">
            <div class="mg-panel mg-panel--compact">
                <div class="mg-panel-label">Guia de Uso</div>
                <h2 class="mg-news-guide__title">Publique comunicados rápidos</h2>
                <p class="mg-page-sub mb-3">Use o feed para avisos, lançamentos e orientações gerais. Os posts ficam salvos em dados locais de Feed.</p>
                <div class="mg-news-guide__stats">
                    <span><strong>{{ $summary['posts'] }}</strong> posts</span>
                    <span><strong>{{ $summary['coach_posts'] }}</strong> do coach</span>
                    <span><strong>{{ $summary['member_posts'] }}</strong> com aluno</span>
                </div>
                <div class="mg-news-guide__links">
                    <a href="{{ route('feed.index') }}"><i class="ri-rss-line"></i> Feed operacional</a>
                    <a href="{{ route('community.index') }}"><i class="ri-community-line"></i> Comunidade</a>
                    <a href="{{ route('patch-notes') }}"><i class="ri-file-list-3-line"></i> Patch notes</a>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
