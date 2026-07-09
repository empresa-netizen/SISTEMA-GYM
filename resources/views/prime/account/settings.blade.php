@extends('layouts.master')

@section('title', 'Configurações')

@section('content')
@php
    $roleLabel = $user->roles->pluck('name')->map(fn ($r) => match ($r) {
        'owner' => 'Dono',
        'manager' => 'Gestor',
        'trainer' => 'Treinador assistente',
        'receptionist' => 'Recepção',
        default => $r,
    })->implode(', ') ?: 'Colaborador';
@endphp

<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Configurações da conta</h1>
            <div class="prime-clients-counters">
                <span class="prime-clients-counter"><i class="ri-user-3-line"></i> {{ $roleLabel }}</span>
                <span class="prime-clients-counter prime-clients-counter--delivered"><i class="ri-building-line"></i> {{ $companyName }}</span>
            </div>
        </div>
        <div class="prime-clients-toolbar__right">
            <a href="{{ route('team.index') }}" class="prime-btn-ghost"><i class="ri-team-line"></i> Colaboradores</a>
            <a href="{{ route('account.subscription') }}" class="prime-btn-ghost"><i class="ri-vip-crown-line"></i> Assinatura</a>
            <a href="{{ route('settings.index') }}" class="prime-btn-primary"><i class="ri-tools-line"></i> Preferências avançadas</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="prime-panel">
                <div class="prime-panel-label mb-3">PERFIL DO TREINADOR</div>
                <form method="POST" action="{{ route('account.profile.update') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="prime-field-label">Nome</label>
                            <input type="text" name="name" class="prime-field" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="prime-field-label">E-mail</label>
                            <input type="email" name="email" class="prime-field" value="{{ old('email', $user->email) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="prime-field-label">Nova senha</label>
                            <input type="password" name="password" class="prime-field" autocomplete="new-password">
                        </div>
                        <div class="col-md-6">
                            <label class="prime-field-label">Confirmar senha</label>
                            <input type="password" name="password_confirmation" class="prime-field" autocomplete="new-password">
                        </div>
                    </div>

                    <div class="prime-panel-label mt-4 mb-3">DADOS DA EMPRESA</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="prime-field-label">Nome da empresa</label>
                            <input type="text" name="company_name" class="prime-field" value="{{ old('company_name', $companyName) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="prime-field-label">E-mail comercial</label>
                            <input type="email" name="company_email" class="prime-field" value="{{ old('company_email', $companyEmail) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="prime-field-label">Telefone</label>
                            <input type="text" name="company_phone" class="prime-field" value="{{ old('company_phone', $companyPhone) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="prime-field-label">Cidade</label>
                            <input type="text" name="company_city" class="prime-field" value="{{ old('company_city', $companyCity) }}">
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="prime-btn-primary"><i class="ri-save-line"></i> Salvar alterações</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="prime-panel prime-panel--compact mb-3">
                <div class="prime-panel-label mb-2">ASSINATURA</div>
                <p class="mb-2">Plano local ativo para operação do coach.</p>
                <p class="small text-muted mb-3">Gerencie renovação e faturas da plataforma.</p>
                <a href="{{ route('account.subscription') }}" class="prime-btn-ghost w-100 justify-content-center">
                    <i class="ri-vip-crown-line"></i> Ver assinatura
                </a>
            </div>

            <div class="prime-panel prime-panel--compact mb-3">
                <div class="prime-panel-label mb-2">EQUIPE</div>
                <p class="mb-2">Convide treinadores assistentes e gestores.</p>
                <a href="{{ route('team.index') }}" class="prime-btn-primary w-100 justify-content-center">
                    <i class="ri-team-line"></i> Gerenciar colaboradores
                </a>
            </div>

            <div class="prime-panel prime-panel--compact">
                <div class="prime-panel-label mb-2">ATALHOS</div>
                <div class="d-grid gap-2">
                    <a href="{{ route('apps.index') }}" class="prime-help-row text-decoration-none"><span><i class="ri-smartphone-line me-2"></i>Apps e integrações</span><i class="ri-arrow-right-s-line"></i></a>
                    <a href="{{ route('products.hub') }}" class="prime-help-row text-decoration-none"><span><i class="ri-shopping-bag-3-line me-2"></i> Produtos</span><i class="ri-arrow-right-s-line"></i></a>
                    <a href="{{ route('settings.index') }}" class="prime-help-row text-decoration-none"><span><i class="ri-settings-3-line me-2"></i> Preferências avançadas</span><i class="ri-arrow-right-s-line"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
