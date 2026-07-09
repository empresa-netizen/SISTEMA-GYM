@extends('layouts.master')

@section('title', 'Meus colaboradores')

@section('css')
<link href="{{ URL::asset('build/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('build/libs/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@push('styles')
<style>
    .prime-collaborators-page {
        display: grid;
        gap: 1rem;
    }

    .prime-collaborators-hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: clamp(1rem, 2.4vw, 1.5rem);
        border: 1px solid var(--prime-border-strong);
        border-radius: 1.25rem;
        background:
            radial-gradient(circle at top left, rgba(59, 130, 246, 0.22), transparent 34%),
            linear-gradient(135deg, rgba(15, 23, 42, 0.98), rgba(8, 12, 20, 0.98));
        box-shadow: var(--prime-shadow);
    }

    .prime-collaborators-actions,
    .prime-collaborators-filter-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.55rem;
    }

    .prime-collaborators-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.75rem;
    }

    .prime-collaborator-stat {
        padding: 0.9rem 1rem;
        border: 1px solid var(--prime-border);
        border-radius: 1rem;
        background: rgba(15, 23, 42, 0.74);
    }

    .prime-collaborator-stat__value {
        color: var(--prime-text);
        font-size: 1.45rem;
        font-weight: 800;
        line-height: 1;
    }

    .prime-collaborators-filter {
        display: grid;
        grid-template-columns: minmax(14rem, 1fr) minmax(10rem, 0.35fr) minmax(10rem, 0.35fr) auto;
        gap: 0.75rem;
        align-items: end;
    }

    .prime-collaborators-table-wrap {
        overflow: hidden;
    }

    .prime-collaborators-table,
    .prime-collaborators-table.dataTable {
        width: 100% !important;
        margin: 0 !important;
        color: var(--prime-text);
        border-collapse: separate !important;
        border-spacing: 0 0.45rem !important;
    }

    .prime-collaborators-table thead th {
        border: 0 !important;
        color: var(--prime-muted);
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        background: transparent !important;
    }

    .prime-collaborators-table tbody tr {
        background: rgba(15, 23, 42, 0.82) !important;
        box-shadow: inset 0 0 0 1px var(--prime-border);
    }

    .prime-collaborators-table tbody td {
        padding: 0.72rem 0.9rem !important;
        border: 0 !important;
        vertical-align: middle;
        color: var(--prime-text);
    }

    .prime-collaborators-table tbody td:first-child {
        border-radius: 0.85rem 0 0 0.85rem;
        color: var(--prime-muted);
        width: 3rem;
    }

    .prime-collaborators-table tbody td:last-child {
        border-radius: 0 0.85rem 0.85rem 0;
    }

    .prime-collaborator-avatar {
        width: 2rem;
        height: 2rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        color: #fff;
        font-weight: 800;
        background: linear-gradient(135deg, #2563eb, #38bdf8);
    }

    .prime-collaborator-name {
        color: var(--prime-text);
        font-weight: 700;
    }

    .prime-collaborator-badge,
    .prime-role-pill,
    .prime-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        border-radius: 999px;
        padding: 0.26rem 0.58rem;
        font-size: 0.72rem;
        font-weight: 700;
    }

    .prime-collaborator-badge,
    .prime-role-pill {
        border: 1px solid rgba(59, 130, 246, 0.28);
        background: rgba(59, 130, 246, 0.13);
        color: #bfdbfe;
    }

    .prime-status-pill--active {
        border: 1px solid rgba(34, 197, 94, 0.32);
        background: rgba(34, 197, 94, 0.13);
        color: #86efac;
    }

    .prime-status-pill--pending {
        border: 1px solid rgba(245, 158, 11, 0.32);
        background: rgba(245, 158, 11, 0.13);
        color: #fcd34d;
    }

    .prime-kebab {
        width: 2rem;
        height: 2rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--prime-border);
        border-radius: 999px;
        color: var(--prime-text);
        background: rgba(148, 163, 184, 0.08);
    }

    .prime-action-menu .dropdown-menu {
        border: 1px solid var(--prime-border-strong);
        background: #0f172a;
        box-shadow: var(--prime-shadow);
    }

    .prime-action-menu .dropdown-item {
        color: var(--prime-text);
        font-size: 0.86rem;
    }

    .prime-action-menu .dropdown-item:hover,
    .prime-action-menu .dropdown-item:focus {
        background: rgba(59, 130, 246, 0.16);
        color: #fff;
    }

    .prime-collaborators-page .dataTables_wrapper .top-container,
    .prime-collaborators-page .dataTables_wrapper .bottom-container {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
        color: var(--prime-muted);
        font-size: 0.82rem;
    }

    .prime-collaborators-page .dataTables_wrapper .top-container {
        margin-bottom: 0.85rem;
    }

    .prime-collaborators-page .dataTables_wrapper .bottom-container {
        margin-top: 0.85rem;
    }

    .prime-collaborators-page .dataTables_filter,
    .prime-collaborators-page .dt-buttons {
        display: none;
    }

    .prime-collaborators-page .dataTables_length select {
        min-width: 4.5rem;
        border-color: var(--prime-border);
        background: rgba(15, 23, 42, 0.9);
        color: var(--prime-text);
    }

    .prime-collaborators-page .page-link {
        border-color: var(--prime-border);
        background: rgba(15, 23, 42, 0.9);
        color: var(--prime-text);
    }

    .prime-collaborators-page .page-item.active .page-link {
        border-color: rgba(59, 130, 246, 0.9);
        background: #2563eb;
    }

    @media (max-width: 992px) {
        .prime-collaborators-filter,
        .prime-collaborators-stats {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
@php
    $roleLabels = [
        'owner' => 'Proprietário',
        'manager' => 'Gerente',
        'trainer' => 'Treinador',
        'receptionist' => 'Recepção',
    ];
    $stats = $collaboratorStats ?? ['total' => 0, 'active' => 0, 'pending' => 0];
    $currentSearch = request('search_value', request('search'));
@endphp

<div class="prime-collaborators-page">
    <span class="visually-hidden">Usuários</span>

    <section class="prime-collaborators-hero">
        <div>
            <p class="prime-panel-label mb-1">Equipe e permissões</p>
            <h1 class="prime-page-title mb-1">Meus colaboradores</h1>
            <p class="prime-page-sub mb-0">Gerencie acessos internos, perfis e senhas usando os usuarios locais da {{ config('brand.name', 'MGTEAM FITNESS & HEALTH') }}.</p>
        </div>
        <div class="prime-collaborators-actions">
            <button type="button" class="prime-btn-ghost" disabled title="Logs locais de usuário ainda não possuem uma tela dedicada">
                <i class="ri-history-line"></i> Logs de colaboradores
            </button>
            @can('create users')
                <a href="{{ route('users.create') }}" class="prime-btn-primary">
                    <i class="ri-user-add-line"></i> Adicionar colaborador
                </a>
            @endcan
        </div>
    </section>

    <div class="prime-collaborators-stats">
        <div class="prime-collaborator-stat">
            <div class="prime-panel-label mb-2">Total local</div>
            <div class="prime-collaborator-stat__value">{{ $stats['total'] }}</div>
            <p class="prime-panel-hint mb-0">Staff vinculado ao tenant</p>
        </div>
        <div class="prime-collaborator-stat">
            <div class="prime-panel-label mb-2">Ativos</div>
            <div class="prime-collaborator-stat__value">{{ $stats['active'] }}</div>
            <p class="prime-panel-hint mb-0">E-mail verificado</p>
        </div>
        <div class="prime-collaborator-stat">
            <div class="prime-panel-label mb-2">Pendentes</div>
            <div class="prime-collaborator-stat__value">{{ $stats['pending'] }}</div>
            <p class="prime-panel-hint mb-0">Aguardando verificação</p>
        </div>
    </div>

    <form method="get" action="{{ url()->current() }}" class="prime-panel prime-panel--compact prime-collaborators-filter">
        <div>
            <label for="searchInput" class="prime-field-label">Buscar nome ou email</label>
            <input
                type="search"
                id="searchInput"
                name="search_value"
                class="prime-field"
                placeholder="Digite nome ou email"
                value="{{ $currentSearch }}"
            >
        </div>
        <div>
            <label for="statusFilter" class="prime-field-label">Status</label>
            <select class="prime-field" id="statusFilter" name="status">
                <option value="">Todos</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Ativo</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendente</option>
            </select>
        </div>
        <div>
            <label for="roleFilter" class="prime-field-label">Perfil</label>
            <select class="prime-field" id="roleFilter" name="role">
                <option value="">Todos os perfis</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                        {{ $roleLabels[$role->name] ?? ucfirst($role->name) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="prime-collaborators-filter-actions">
            <button type="submit" class="prime-btn-primary">
                <i class="ri-filter-3-line"></i> Filtrar
            </button>
            <a href="{{ url()->current() }}" id="resetFilters" class="prime-btn-ghost">
                <i class="ri-close-line"></i> Limpar
            </a>
        </div>
    </form>

    <section class="prime-panel prime-collaborators-table-wrap">
        <div class="table-responsive">
            {!! $dataTable->table() !!}
        </div>
    </section>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@section('script')
{!! $dataTable->scripts() !!}

<script>
    $('#roleFilter, #statusFilter').on('change', function() {
        $(this).closest('form').trigger('submit');
    });
</script>
@endsection
