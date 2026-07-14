@extends('layouts.master')

@section('title', 'Comunidade')

@section('content')
<div class="mg-news-page mg-community-page">
    <div class="mg-news-shell mg-community-shell">
        <main class="mg-news-main">
            <div class="mg-news-header mg-community-header">
                <div>
                    <h1 class="mg-page-title mb-0">Comunidade</h1>
                    <p class="mg-page-sub mb-0">Posts locais da comunidade, organizados por grupo e aluno.</p>
                </div>
                <button type="button" class="mg-btn-primary" data-bs-toggle="modal" data-bs-target="#newGroupModal">
                    <i class="ri-add-line"></i> Novo grupo
                </button>
            </div>

            <div class="mg-news-list">
                @forelse($recentPosts as $post)
                    @php
                        $author = $post->member?->name ?: 'Coach';
                        $initials = collect(explode(' ', trim($author)))
                            ->filter()
                            ->take(2)
                            ->map(fn ($part) => mb_substr($part, 0, 1))
                            ->implode('');
                        $avatar = $post->member?->photo ? asset('storage/'.$post->member->photo) : null;
                        $group = $post->group;
                        $comments = 0;
                    @endphp
                    <article class="mg-news-card mg-community-card">
                        <header class="mg-news-card__header">
                            @if($avatar)
                                <img src="{{ $avatar }}" alt="" class="mg-news-avatar">
                            @else
                                <div class="mg-news-avatar">{{ strtoupper($initials ?: 'C') }}</div>
                            @endif
                            <div class="mg-news-card__identity">
                                <strong>{{ $author }}</strong>
                                <span>{{ $post->created_at?->diffForHumans() }} · {{ $post->created_at?->format('d/m/Y H:i') }}</span>
                            </div>
                            @if($group)
                                <a href="{{ route('community.show', $group) }}" class="mg-chip mg-chip--info text-decoration-none">{{ $group->name }}</a>
                            @endif
                        </header>

                        <div class="mg-news-card__media mg-community-card__media" aria-label="Imagem do post">
                            <i class="ri-image-line"></i>
                            <span>{{ $group?->name ?? 'Comunidade' }}</span>
                        </div>

                        <div class="mg-news-card__content mg-community-card__caption">
                            <p>{{ $post->content }}</p>
                        </div>

                        <footer class="mg-news-card__footer">
                            <span><i class="ri-heart-3-line"></i> {{ $post->likes_count }} curtidas</span>
                            <span><i class="ri-chat-3-line"></i> {{ $comments }} comentários</span>
                        </footer>
                    </article>
                @empty
                    <div class="mg-empty-state mg-news-empty">
                        <i class="ri-community-line"></i>
                        <p>Nenhum post local de comunidade ainda.</p>
                        @if($groups->isEmpty())
                            <button type="button" class="mg-btn-primary" data-bs-toggle="modal" data-bs-target="#newGroupModal">
                                <i class="ri-add-line"></i> Criar grupo inicial
                            </button>
                        @else
                            <a href="{{ route('community.show', $groups->first()) }}" class="mg-btn-primary">
                                <i class="ri-send-plane-line"></i> Publicar no grupo
                            </a>
                        @endif
                    </div>
                @endforelse
            </div>

            @if($recentPosts->hasPages())
                <div class="mg-pagination">{{ $recentPosts->links() }}</div>
            @endif
        </main>

        <aside class="mg-news-guide mg-community-moderation">
            <div class="mg-panel mg-panel--compact">
                <div class="mg-panel-label">Moderação da Comunidade</div>
                <h2 class="mg-news-guide__title">Notas de acompanhamento</h2>
                <p class="mg-page-sub mb-3">Este painel usa somente dados locais de grupos e posts da comunidade.</p>

                <div class="mg-news-guide__stats">
                    <span><strong>{{ $summary['posts'] }}</strong> posts locais</span>
                    <span><strong>{{ $summary['likes'] }}</strong> curtidas</span>
                    <span><strong>{{ $summary['groups'] }}</strong> grupos</span>
                    <span><strong>{{ $summary['members'] }}</strong> membros estimados</span>
                    <span><strong>{{ $summary['reported'] }}</strong> denúncias abertas</span>
                </div>

                <div class="mg-community-notes">
                    <div>
                        <i class="ri-shield-check-line"></i>
                        Revisar manualmente captions com dúvidas ou pedidos de suporte.
                    </div>
                    <div>
                        <i class="ri-chat-3-line"></i>
                        Comentários ainda não possuem tabela local conectada.
                    </div>
                    <div>
                        <i class="ri-image-line"></i>
                        Imagens de posts aguardam campo de mídia local.
                    </div>
                </div>

                @if($groups->count())
                    <div class="mg-news-guide__links mt-3">
                        @foreach($groups->take(4) as $group)
                            <a href="{{ route('community.show', $group) }}">
                                <i class="ri-group-line"></i>
                                <span>{{ $group->name }}</span>
                                <strong>{{ $group->posts_count }}</strong>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </aside>
    </div>
</div>

<div class="modal fade" id="newGroupModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('community.groups.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Novo grupo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="mg-field-label">Nome</label>
                    <input type="text" name="name" class="mg-field" required>
                </div>
                <div class="mb-3">
                    <label class="mg-field-label">Descrição</label>
                    <textarea name="description" class="mg-field" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="mg-btn-ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="mg-btn-primary">Criar grupo</button>
            </div>
        </form>
    </div>
</div>
@endsection
