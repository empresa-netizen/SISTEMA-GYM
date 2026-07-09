@extends('layouts.master')

@section('title', 'Treinos Predefinidos')

@section('content')
@php
    $hasFilters = ($filters['q'] ?? '') !== ''
        || ($filters['status'] ?? '') !== ''
        || ($filters['level'] ?? '') !== '';
@endphp

<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Treinos Predefinidos</h1>
            <div class="prime-clients-counters">
                <span class="prime-clients-counter">
                    <i class="ri-list-check-3"></i>
                    {{ $templates->total() }} templates
                </span>
            </div>
        </div>
        <div class="prime-clients-toolbar__right">
            <a href="{{ route('library.workout') }}" class="prime-btn-ghost">
                <i class="ri-arrow-left-line"></i> Voltar
            </a>
            <button type="button" class="prime-btn-primary" data-bs-toggle="modal" data-bs-target="#workoutTemplateModal">
                <i class="ri-add-line"></i> Novo template
            </button>
        </div>
    </div>

    <p class="prime-page-sub mb-0">Modelos reutilizáveis da biblioteca. Importe e atribua a um cliente quando precisar.</p>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <form method="GET" action="{{ route('workout-templates.index') }}" class="prime-panel prime-panel--compact mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="prime-field-label">Buscar</label>
                <input type="search" name="q" value="{{ $filters['q'] }}" class="prime-field" placeholder="Título ou foco">
            </div>
            <div class="col-md-3">
                <label class="prime-field-label">Status</label>
                <select name="status" class="prime-field">
                    <option value="">Todos</option>
                    @foreach($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="prime-field-label">Nível</label>
                <select name="level" class="prime-field">
                    <option value="">Todos</option>
                    @foreach($levelLabels as $value => $label)
                        <option value="{{ $value }}" @selected($filters['level'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="prime-btn-primary w-100" type="submit"><i class="ri-filter-3-line"></i> Filtrar</button>
                @if($hasFilters)
                    <a href="{{ route('workout-templates.index') }}" class="prime-btn-ghost" title="Limpar"><i class="ri-close-line"></i></a>
                @endif
            </div>
        </div>
    </form>

    <div class="prime-client-list">
        @forelse($templates as $template)
            <div class="prime-client-card">
                <div class="prime-client-card__main">
                    <div class="prime-client-card__avatar" style="background:linear-gradient(135deg,#0f766e,#2dd4bf)">
                        <i class="ri-run-line"></i>
                    </div>
                    <div class="prime-client-card__identity">
                        <div class="prime-client-card__name">{{ $template->title }}</div>
                        <div class="prime-client-card__meta">
                            @if($template->focus)<span>{{ $template->focus }}</span>@endif
                            @if($template->duration_weeks)<span>{{ $template->duration_weeks }} semanas</span>@endif
                            @if($template->sessions_per_week)<span>{{ $template->sessions_per_week }}x/semana</span>@endif
                            <span>{{ $levelLabels[$template->level] ?? $template->level }}</span>
                        </div>
                        <div class="prime-client-chips">
                            @if($template->status === 'published')
                                <span class="prime-chip prime-chip--success">Publicado</span>
                            @elseif($template->status === 'archived')
                                <span class="prime-chip">Arquivado</span>
                            @else
                                <span class="prime-chip">Rascunho</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="prime-client-card__actions">
                    <form method="POST" action="{{ route('workout-templates.assign', $template) }}" class="d-flex gap-2 align-items-center">
                        @csrf
                        <select name="member_id" class="prime-field prime-field--sm" required>
                            <option value="">Atribuir a...</option>
                            @foreach($members as $member)
                                <option value="{{ $member->id }}">{{ $member->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="prime-btn-ghost" title="Importar para cliente">
                            <i class="ri-user-shared-line"></i> Importar
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="prime-empty-state">
                <i class="ri-list-check-3"></i>
                <p>Nenhum template de treino na biblioteca.</p>
                <button type="button" class="prime-btn-primary" data-bs-toggle="modal" data-bs-target="#workoutTemplateModal">
                    <i class="ri-add-line"></i> Criar primeiro template
                </button>
            </div>
        @endforelse
    </div>

    @if($templates->hasPages())
        <div class="prime-pagination">{{ $templates->links() }}</div>
    @endif
</div>

<div class="modal fade" id="workoutTemplateModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('workout-templates.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Novo template de treino</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Título</label>
                    <input type="text" name="title" class="form-control" placeholder="Ex: Full Body 3x" required>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Foco</label>
                        <input type="text" name="focus" class="form-control" placeholder="Hipertrofia, força...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nível</label>
                        <select name="level" class="form-select">
                            @foreach($levelLabels as $value => $label)
                                <option value="{{ $value }}" @selected($value === 'intermediate')>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Semanas</label>
                        <input type="number" name="duration_weeks" class="form-control" min="1" max="52" value="4">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sessões/semana</label>
                        <input type="number" name="sessions_per_week" class="form-control" min="1" max="14" value="3">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="draft">Rascunho</option>
                            <option value="published" selected>Publicado</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Descrição</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar template</button>
            </div>
        </form>
    </div>
</div>
@endsection
