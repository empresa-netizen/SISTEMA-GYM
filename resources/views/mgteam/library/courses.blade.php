@extends('layouts.master')

@section('title', 'Meus Cursos')

@section('content')
@php
    $hasFilters = ($filters['q'] ?? '') !== ''
        || ($filters['status'] ?? '') !== ''
        || ($filters['product'] ?? '') !== ''
        || ($filters['sort'] ?? 'recent') !== 'recent';
    $statusChip = [
        'draft' => '',
        'published' => 'mg-chip--success',
        'archived' => 'mg-chip--warn',
    ];
@endphp

<div class="mg-clients-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Meus Cursos</h1>
            <p class="mg-page-sub mb-0">Gerencie seus cursos, módulos e aulas</p>
        </div>
        <div class="mg-clients-toolbar__right">
            <button type="button" class="mg-btn-primary" data-bs-toggle="modal" data-bs-target="#courseModal">
                <i class="ri-add-line"></i> Criar Curso
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <form method="GET" action="{{ route('library.courses') }}" class="mg-panel mg-panel--compact">
        <div class="row g-2 align-items-end">
            <div class="col-xl-4 col-md-6">
                <label class="mg-field-label">Buscar</label>
                <input type="search" name="q" value="{{ $filters['q'] }}" class="mg-field" placeholder="Buscar curso, módulo ou aula">
            </div>
            <div class="col-xl-2 col-md-6">
                <label class="mg-field-label">Status</label>
                <select name="status" class="mg-field">
                    <option value="">Todos os status</option>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-xl-2 col-md-6">
                <label class="mg-field-label">Produto</label>
                <select name="product" class="mg-field">
                    <option value="">Todos os produtos</option>
                    @foreach($products ?? [] as $product)
                        <option value="{{ $product }}" @selected($filters['product'] === $product)>{{ $product }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-xl-2 col-md-6">
                <label class="mg-field-label">Ordenar</label>
                <select name="sort" class="mg-field">
                    @foreach($sortOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-xl-2 col-md-12 d-flex gap-2">
                <button type="submit" class="mg-btn-primary w-100">
                    <i class="ri-filter-3-line"></i> Filtrar
                </button>
                @if($hasFilters)
                    <a href="{{ route('library.courses') }}" class="mg-btn-ghost" title="Limpar filtros">
                        <i class="ri-close-line"></i>
                    </a>
                @endif
            </div>
        </div>
    </form>

    <div class="mg-client-list">
        @forelse($courses as $course)
            <div class="mg-client-card">
                <div class="mg-client-card__main">
                    <div class="mg-client-card__avatar" style="background:linear-gradient(135deg,#4f46e5,#8b5cf6)">
                        <i class="ri-graduation-cap-line"></i>
                    </div>
                    <div class="mg-client-card__identity">
                        <div class="mg-client-card__name">{{ $course->title }}</div>
                        <div class="mg-client-card__meta">
                            @if($course->product)<span>{{ $course->product }}</span>@endif
                            <span>{{ $course->modules_count }} módulos</span>
                            <span>{{ $course->lessons_count }} aulas</span>
                        </div>
                        <div class="mg-client-chips">
                            <span class="mg-chip {{ $statusChip[$course->status] ?? '' }}">{{ $statusOptions[$course->status] ?? $course->status }}</span>
                        </div>
                    </div>
                </div>
                <div class="mg-client-card__actions">
                    <span class="mg-chip" title="Pronto para vincular a um produto/cliente">Biblioteca</span>
                </div>
            </div>
        @empty
            <div class="mg-empty-state">
                <i class="ri-graduation-cap-line"></i>
                <p>Nenhum curso cadastrado</p>
                <button type="button" class="mg-btn-primary" data-bs-toggle="modal" data-bs-target="#courseModal">
                    <span aria-hidden="true">+</span> Criar MGTEAMiro Curso
                </button>
            </div>
        @endforelse
    </div>

    @if(method_exists($courses, 'hasPages') && $courses->hasPages())
        <div class="mg-pagination">{{ $courses->links() }}</div>
    @endif
</div>

<div class="modal fade" id="courseModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('library.courses.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Criar curso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Título</label>
                    <input type="text" name="title" class="form-control" placeholder="Ex: Protocolo de emagrecimento" required>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Produto vinculado</label>
                        <input type="text" name="product" class="form-control" placeholder="Consultoria, Mentoria...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="draft">Rascunho</option>
                            <option value="published" selected>Publicado</option>
                            <option value="archived">Arquivado</option>
                        </select>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Módulos</label>
                        <input type="number" name="modules_count" class="form-control" min="0" value="1">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Aulas</label>
                        <input type="number" name="lessons_count" class="form-control" min="0" value="4">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Descrição</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar curso</button>
            </div>
        </form>
    </div>
</div>
@endsection
