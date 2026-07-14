@extends('layouts.master')

@section('title', 'Meus colaboradores')

@section('css')
<link href="{{ URL::asset('build/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('build/libs/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@push('styles')
<style>
    .mg-collaborators-page {
        display: grid;
        gap: 0.85rem;
    }

    .mg-collaborators-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.62rem;
    }

    .mg-collaborator-stat {
        padding: 0.72rem 0.82rem;
        border: 1px solid #DDE5EF;
        border-radius: 0.82rem;
        background: #FFFFFF;
        box-shadow: 0 8px 22px rgba(23, 37, 56, 0.04);
    }

    .mg-collaborator-stat__value {
        color: #101929;
        font-size: 1.18rem;
        font-weight: 930;
        line-height: 1;
    }

    .mg-collaborators-filter {
        display: grid;
        grid-template-columns: minmax(14rem, 1fr) minmax(10rem, 0.35fr) minmax(10rem, 0.35fr) auto;
        gap: 0.52rem;
        align-items: end;
    }

    .mg-collaborators-table-wrap {
        overflow: hidden;
        padding: 0.72rem;
        border: 1px solid #DDE5EF;
        border-radius: 0.9rem;
        background: #FFFFFF;
        box-shadow: 0 10px 28px rgba(23, 37, 56, 0.045);
    }

    .mg-collaborators-table,
    .mg-collaborators-table.dataTable {
        width: 100% !important;
        margin: 0 !important;
        color: #1D2C43;
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }

    .mg-collaborators-table thead th {
        padding: 0.58rem 0.68rem !important;
        border: 0 !important;
        border-bottom: 1px solid #E5EBF3 !important;
        background: #F6F8FB !important;
        color: #7A899F;
        font-size: 0.68rem;
        font-weight: 920;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .mg-collaborators-table tbody td {
        padding: 0.58rem 0.68rem !important;
        border: 0 !important;
        border-bottom: 1px solid #EDF1F6 !important;
        color: #1D2C43;
        font-size: 0.78rem;
        vertical-align: middle;
    }

    .mg-collaborator-avatar {
        display: inline-flex;
        width: 2rem;
        height: 2rem;
        align-items: center;
        justify-content: center;
        border-radius: 0.66rem;
        background: linear-gradient(135deg, #15365F, #3B95FF);
        color: #FFFFFF;
        font-size: 0.78rem;
        font-weight: 900;
    }

    .mg-collaborator-name {
        color: #101929;
        font-weight: 890;
    }

    .mg-collaborator-badge,
    .mg-role-pill,
    .mg-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.22rem;
        border-radius: 999px;
        padding: 0.2rem 0.48rem;
        font-size: 0.68rem;
        font-weight: 880;
    }

    .mg-collaborator-badge,
    .mg-role-pill {
        border: 1px solid rgba(59, 149, 255, 0.24);
        background: rgba(59, 149, 255, 0.09);
        color: #246EC8;
    }

    .mg-status-pill--active {
        border: 1px solid rgba(22, 138, 70, 0.22);
        background: #E8F8EF;
        color: #168A46;
    }

    .mg-status-pill--pending {
        border: 1px solid rgba(245, 158, 11, 0.26);
        background: #FFF5D8;
        color: #9B6A00;
    }

    .mg-kebab {
        display: inline-flex;
        width: 1.9rem;
        height: 1.9rem;
        align-items: center;
        justify-content: center;
        border: 1px solid #DCE5EF;
        border-radius: 0.6rem;
        background: #FFFFFF;
        color: #2C3E59;
    }

    .mg-action-menu .dropdown-menu {
        border: 1px solid #DDE5EF;
        border-radius: 0.72rem;
        background: #FFFFFF;
        box-shadow: 0 18px 44px rgba(15, 28, 46, 0.16);
    }

    .mg-action-menu .dropdown-item {
        color: #263852;
        font-size: 0.78rem;
        font-weight: 760;
    }

    .mg-action-menu .dropdown-item:hover,
    .mg-action-menu .dropdown-item:focus {
        background: #EEF5FF;
        color: #246EC8;
    }

    .mg-collaborators-page .dataTables_wrapper .top-container,
    .mg-collaborators-page .dataTables_wrapper .bottom-container {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 0.7rem;
        color: #76869D;
        font-size: 0.76rem;
        font-weight: 720;
    }

    .mg-collaborators-page .dataTables_wrapper .top-container {
        margin-bottom: 0.62rem;
    }

    .mg-collaborators-page .dataTables_wrapper .bottom-container {
        margin-top: 0.62rem;
    }

    .mg-collaborators-page .dataTables_filter,
    .mg-collaborators-page .dt-buttons {
        display: none;
    }

    .mg-collaborators-page .dataTables_length select {
        min-width: 4.4rem;
        border-color: #DCE5EF;
        border-radius: 0.58rem;
        background: #FFFFFF;
        color: #1D2C43;
    }

    .mg-collaborators-page .page-link {
        border-color: #DCE5EF;
        background: #FFFFFF;
        color: #30425D;
        font-size: 0.76rem;
        font-weight: 800;
    }

    .mg-collaborators-page .page-item.active .page-link {
        border-color: #3B95FF;
        background: #3B95FF;
        color: #FFFFFF;
    }

    .mg-team-modal .modal-dialog {
        max-width: 680px;
    }

    .mg-team-modal .modal-content {
        overflow: hidden;
        border: 1px solid #D7DFEA;
        border-radius: 1rem;
        background: #F6F8FB;
        box-shadow: 0 24px 80px rgba(15, 28, 46, 0.24);
    }

    .mg-team-modal .modal-header,
    .mg-team-modal .modal-footer {
        padding: 0.78rem 1rem;
        border-color: #DDE5EF;
        background: #FFFFFF;
    }

    .mg-team-modal .modal-body {
        padding: 0.92rem;
    }

    .mg-team-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.5rem;
    }

    @media (max-width: 992px) {
        .mg-collaborators-filter,
        .mg-collaborators-stats,
        .mg-team-form-grid {
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

<div class="mg-collaborators-page">
    <span class="visually-hidden">Usuários</span>

    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Meus colaboradores</h1>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter"><i class="ri-team-line"></i> {{ $stats['total'] }} usuários</span>
                <span class="mg-clients-counter mg-clients-counter--delivered"><i class="ri-checkbox-circle-line"></i> {{ $stats['active'] }} ativos</span>
                <span class="mg-clients-counter mg-clients-counter--pending"><i class="ri-time-line"></i> {{ $stats['pending'] }} pendentes</span>
            </div>
        </div>
        <div class="mg-clients-toolbar__right">
            <a href="{{ route('account.settings') }}" class="mg-btn-ghost"><i class="ri-arrow-left-line"></i> Conta</a>
            @can('create users')
                <button type="button" class="mg-btn-primary" data-bs-toggle="modal" data-bs-target="#teamInviteModal">
                    <i class="ri-user-add-line"></i> Adicionar membro
                </button>
            @endcan
        </div>
    </div>

    <p class="mg-page-sub mb-0">Gerencie treinadores assistentes, gestores e permissões usando roles Spatie.</p>

    <div class="mg-collaborators-stats">
        <div class="mg-collaborator-stat">
            <div class="mg-panel-label mb-2">Total local</div>
            <div class="mg-collaborator-stat__value">{{ $stats['total'] }}</div>
            <p class="mg-panel-hint mb-0">Staff vinculado ao tenant</p>
        </div>
        <div class="mg-collaborator-stat">
            <div class="mg-panel-label mb-2">Ativos</div>
            <div class="mg-collaborator-stat__value">{{ $stats['active'] }}</div>
            <p class="mg-panel-hint mb-0">E-mail verificado</p>
        </div>
        <div class="mg-collaborator-stat">
            <div class="mg-panel-label mb-2">Pendentes</div>
            <div class="mg-collaborator-stat__value">{{ $stats['pending'] }}</div>
            <p class="mg-panel-hint mb-0">Aguardando verificação</p>
        </div>
    </div>

    <form method="get" action="{{ url()->current() }}" class="mg-panel mg-panel--compact mg-collaborators-filter">
        <div>
            <label for="searchInput" class="mg-field-label">Buscar nome ou email</label>
            <input
                type="search"
                id="searchInput"
                name="search_value"
                class="mg-field"
                placeholder="Digite nome ou email"
                value="{{ $currentSearch }}"
            >
        </div>
        <div>
            <label for="statusFilter" class="mg-field-label">Status</label>
            <select class="mg-field" id="statusFilter" name="status">
                <option value="">Todos</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Ativo</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendente</option>
            </select>
        </div>
        <div>
            <label for="roleFilter" class="mg-field-label">Perfil</label>
            <select class="mg-field" id="roleFilter" name="role">
                <option value="">Todos os perfis</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                        {{ $roleLabels[$role->name] ?? ucfirst($role->name) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="mg-btn-primary">
                <i class="ri-filter-3-line"></i> Filtrar
            </button>
            <a href="{{ url()->current() }}" id="resetFilters" class="mg-btn-ghost">
                <i class="ri-close-line"></i> Limpar
            </a>
        </div>
    </form>

    <section class="mg-collaborators-table-wrap">
        <div class="table-responsive">
            {!! $dataTable->table() !!}
        </div>
    </section>
</div>

@can('create users')
    <div class="modal fade mg-team-modal" id="teamInviteModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('users.store') }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">Adicionar membro da equipe</h5>
                        <p class="mg-panel-hint mb-0">Crie o acesso e atribua o perfil operacional.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mg-team-form-grid">
                        <div>
                            <label class="mg-field-label">Nome</label>
                            <input type="text" name="name" class="mg-field" value="{{ old('name') }}" placeholder="Nome do colaborador" required>
                        </div>
                        <div>
                            <label class="mg-field-label">E-mail</label>
                            <input type="email" name="email" class="mg-field" value="{{ old('email') }}" placeholder="email@empresa.com" required>
                        </div>
                        <div>
                            <label class="mg-field-label">Perfil</label>
                            <select name="role" class="mg-field" required>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" @selected(old('role', 'trainer') === $role->name)>
                                        {{ $roleLabels[$role->name] ?? ucfirst($role->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mg-field-label">Avatar URL</label>
                            <input type="text" name="avatar" class="mg-field" value="{{ old('avatar') }}" placeholder="Opcional">
                        </div>
                        <div>
                            <label class="mg-field-label">Senha inicial</label>
                            <input type="password" name="password" class="mg-field" autocomplete="new-password" required>
                        </div>
                        <div>
                            <label class="mg-field-label">Confirmar senha</label>
                            <input type="password" name="password_confirmation" class="mg-field" autocomplete="new-password" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="mg-btn-ghost" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="mg-btn-primary"><i class="ri-user-add-line"></i> Adicionar</button>
                </div>
            </form>
        </div>
    </div>
@endcan

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

    document.addEventListener('DOMContentLoaded', function () {
        const shouldOpenModal = @json($errors->any());
        const modalElement = document.getElementById('teamInviteModal');

        if (shouldOpenModal && modalElement && window.bootstrap) {
            new bootstrap.Modal(modalElement).show();
        }
    });
</script>
@endsection
