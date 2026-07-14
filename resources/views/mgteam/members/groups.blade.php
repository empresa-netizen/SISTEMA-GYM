@extends('layouts.master')

@section('title', 'Meus grupos')

@section('content')
<div class="mg-clients-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Meus grupos</h1>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter">
                    <i class="ri-team-line"></i>
                    {{ $groups->total() }} encontrados
                </span>
                <span class="mg-clients-counter mg-clients-counter--delivered">
                    <i class="ri-user-line"></i>
                    {{ $stats['total_members'] ?? 0 }} membros mapeados
                </span>
            </div>
        </div>
        <div class="mg-clients-toolbar__right">
            <a href="{{ route('community.index') }}" class="mg-btn-ghost">
                <i class="ri-community-line"></i> Comunidade
            </a>
            <button type="button" class="mg-btn-primary" data-bs-toggle="modal" data-bs-target="#newGroupModal">
                <i class="ri-add-line"></i> Novo Grupo
            </button>
        </div>
    </div>

    <p class="mg-page-sub mb-0">Organize comunidades e acompanhe os grupos cadastrados com dados locais.</p>

    <form method="GET" class="mg-panel mg-panel--compact">
        <div class="row g-2 align-items-end">
            <div class="col-xl-3 col-md-6">
                <label class="mg-field-label">Buscar grupo</label>
                <input type="search" name="q" value="{{ $filters['q'] }}" class="mg-field" placeholder="Nome ou descrição">
            </div>
            <div class="col-xl-2 col-md-6">
                <label class="mg-field-label">Membros</label>
                <select name="members_count" class="mg-field">
                    <option value="">Todos</option>
                    <option value="empty" @selected($filters['members_count'] === 'empty')>Sem membros</option>
                    <option value="1_10" @selected($filters['members_count'] === '1_10')>1 a 10</option>
                    <option value="11_50" @selected($filters['members_count'] === '11_50')>11 a 50</option>
                    <option value="51_plus" @selected($filters['members_count'] === '51_plus')>51+</option>
                </select>
            </div>
            <div class="col-xl-2 col-md-6">
                <label class="mg-field-label">Plano</label>
                <select class="mg-field" disabled title="CommunityGroup local não possui vínculo com plano">
                    <option>{{ $plans->count() ? 'Sem vínculo local' : 'Sem planos locais' }}</option>
                </select>
            </div>
            <div class="col-xl-2 col-md-6">
                <label class="mg-field-label">Sexo</label>
                <select class="mg-field" disabled title="CommunityGroup local não possui vínculo com sexo">
                    <option>Sem vínculo local</option>
                </select>
            </div>
            <div class="col-xl-2 col-md-6">
                <label class="mg-field-label">Sync</label>
                <select class="mg-field" disabled title="CommunityGroup local não possui flags de sincronização">
                    <option>Sem flags locais</option>
                </select>
            </div>
            <div class="col-xl-1 col-md-6 d-flex gap-2">
                <button type="submit" class="mg-btn-primary w-100" title="Filtrar">
                    <i class="ri-search-line"></i>
                </button>
                @if($filters['q'] !== '' || filled($filters['members_count']))
                    <a href="{{ url()->current() }}" class="mg-btn-ghost" title="Limpar filtros">
                        <i class="ri-close-line"></i>
                    </a>
                @endif
            </div>
        </div>
    </form>

    <div class="mg-client-list">
        @forelse($groups as $group)
            <a href="{{ route('community.show', $group) }}" class="mg-client-card text-decoration-none">
                <div class="mg-client-card__main">
                    <div class="mg-client-card__avatar" style="background:linear-gradient(135deg,#0f766e,#14b8a6)">
                        <i class="ri-team-line"></i>
                    </div>
                    <div class="mg-client-card__identity">
                        <div class="mg-client-card__name">{{ $group->name }}</div>
                        <div class="mg-client-card__meta">
                            <span>{{ $group->members_count ?? 0 }} membros</span>
                        </div>
                        @if($group->description)
                            <p class="mg-hub-excerpt">{{ Str::limit($group->description, 120) }}</p>
                        @else
                            <p class="mg-hub-excerpt">Grupo local sem descrição cadastrada.</p>
                        @endif
                    </div>
                </div>
                <div class="mg-client-card__actions">
                    <span class="mg-chip mg-chip--info">
                        <i class="ri-user-line"></i> {{ $group->members_count ?? 0 }}
                    </span>
                    <span class="mg-chip">
                        <i class="ri-chat-3-line"></i> {{ $group->posts_count ?? 0 }}
                    </span>
                    <i class="ri-arrow-right-s-line mg-client-card__chevron"></i>
                </div>
            </a>
        @empty
            <div class="mg-empty-state">
                <i class="ri-team-line"></i>
                <p>{{ ($filters['q'] !== '' || filled($filters['members_count'])) ? 'Nenhum grupo encontrado com os filtros atuais.' : 'Nenhum grupo cadastrado ainda. Crie o grupo inicial para reunir seus alunos.' }}</p>
                <button type="button" class="mg-btn-primary" data-bs-toggle="modal" data-bs-target="#newGroupModal">
                    <i class="ri-add-line"></i> Novo Grupo
                </button>
            </div>
        @endforelse
    </div>

    @if($groups->hasPages())
        <div class="mg-pagination">{{ $groups->links() }}</div>
    @endif
</div>

<div class="modal fade" id="newGroupModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('community.groups.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Novo Grupo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="mg-field-label">Nome</label>
                    <input type="text" name="name" class="mg-field" required>
                </div>
                <div class="mb-3">
                    <label class="mg-field-label">Descrição</label>
                    <textarea name="description" class="mg-field" rows="3"></textarea>
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
