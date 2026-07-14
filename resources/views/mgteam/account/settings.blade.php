@extends('layouts.master')

@section('title', 'Configurações')

@php
    $roleLabel = $user->roles->pluck('name')->map(fn ($roleName) => match ($roleName) {
        'owner' => 'Dono',
        'manager' => 'Gestor',
        'trainer' => 'Treinador assistente',
        'receptionist' => 'Recepção',
        default => $roleName,
    })->implode(', ') ?: 'Colaborador';
@endphp

@push('styles')
    <style>
        .mg-settings-page {
            display: grid;
            gap: 0.85rem;
        }

        .mg-settings-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 20rem;
            gap: 0.85rem;
            align-items: start;
        }

        .mg-settings-card {
            padding: 0.82rem;
            border: 1px solid #DDE5EF;
            border-radius: 0.9rem;
            background: #FFFFFF;
            box-shadow: 0 10px 28px rgba(23, 37, 56, 0.045);
        }

        .mg-settings-section + .mg-settings-section {
            margin-top: 0.78rem;
            padding-top: 0.78rem;
            border-top: 1px solid #E6ECF4;
        }

        .mg-settings-section__head {
            display: flex;
            justify-content: space-between;
            gap: 0.8rem;
            align-items: center;
            margin-bottom: 0.58rem;
        }

        .mg-settings-section__title {
            margin: 0;
            color: #132037;
            font-size: 0.82rem;
            font-weight: 920;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .mg-settings-section__hint {
            margin: 0.14rem 0 0;
            color: #7B8BA4;
            font-size: 0.72rem;
            font-weight: 700;
        }

        .mg-settings-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.5rem;
        }

        .mg-settings-side {
            display: grid;
            gap: 0.62rem;
        }

        .mg-settings-side-card {
            padding: 0.74rem;
            border: 1px solid #DDE5EF;
            border-radius: 0.82rem;
            background: #FFFFFF;
            box-shadow: 0 8px 22px rgba(23, 37, 56, 0.04);
        }

        .mg-settings-side-card p {
            color: #6D7C92;
            font-size: 0.76rem;
            font-weight: 680;
            line-height: 1.35;
        }

        .mg-settings-shortcuts {
            display: grid;
            gap: 0.36rem;
        }

        .mg-settings-shortcut {
            display: flex;
            justify-content: space-between;
            gap: 0.72rem;
            align-items: center;
            min-height: 2.45rem;
            padding: 0 0.64rem;
            border: 1px solid #E2E8F1;
            border-radius: 0.66rem;
            background: #F8FAFD;
            color: #263852;
            font-size: 0.76rem;
            font-weight: 830;
            text-decoration: none;
        }

        .mg-settings-shortcut:hover {
            border-color: #BFD4F2;
            background: #EEF5FF;
            color: #246EC8;
        }

        @media (max-width: 991.98px) {
            .mg-settings-grid,
            .mg-settings-form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
<div class="mg-clients-page mg-settings-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Configurações da conta</h1>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter"><i class="ri-user-3-line"></i> {{ $roleLabel }}</span>
                <span class="mg-clients-counter mg-clients-counter--delivered"><i class="ri-building-line"></i> {{ $companyName }}</span>
            </div>
        </div>
        <div class="mg-clients-toolbar__right">
            <a href="{{ route('team.index') }}" class="mg-btn-ghost"><i class="ri-team-line"></i> Colaboradores</a>
            <a href="{{ route('account.subscription') }}" class="mg-btn-ghost"><i class="ri-vip-crown-line"></i> Assinatura</a>
            <a href="{{ route('settings.index') }}" class="mg-btn-primary"><i class="ri-tools-line"></i> Preferências</a>
        </div>
    </div>

    <div class="mg-settings-grid">
        <form method="POST" action="{{ route('account.profile.update') }}" class="mg-settings-card">
            @csrf
            <div class="mg-settings-section">
                <div class="mg-settings-section__head">
                    <div>
                        <h2 class="mg-settings-section__title">Perfil do coach</h2>
                        <p class="mg-settings-section__hint">Dados de acesso e identidade do usuário logado.</p>
                    </div>
                    <span class="mg-chip mg-chip--info">{{ $roleLabel }}</span>
                </div>
                <div class="mg-settings-form-grid">
                    <div>
                        <label class="mg-field-label">Nome</label>
                        <input type="text" name="name" class="mg-field" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div>
                        <label class="mg-field-label">E-mail</label>
                        <input type="email" name="email" class="mg-field" value="{{ old('email', $user->email) }}" required>
                    </div>
                    <div>
                        <label class="mg-field-label">Nova senha</label>
                        <input type="password" name="password" class="mg-field" autocomplete="new-password" placeholder="Opcional">
                    </div>
                    <div>
                        <label class="mg-field-label">Confirmar senha</label>
                        <input type="password" name="password_confirmation" class="mg-field" autocomplete="new-password" placeholder="Repetir nova senha">
                    </div>
                </div>
            </div>

            <div class="mg-settings-section">
                <div class="mg-settings-section__head">
                    <div>
                        <h2 class="mg-settings-section__title">Dados da empresa</h2>
                        <p class="mg-settings-section__hint">Informações usadas em comunicações, faturas e cabeçalhos.</p>
                    </div>
                    <span class="mg-chip">{{ $tenant->email }}</span>
                </div>
                <div class="mg-settings-form-grid">
                    <div>
                        <label class="mg-field-label">Nome da empresa</label>
                        <input type="text" name="company_name" class="mg-field" value="{{ old('company_name', $companyName) }}">
                    </div>
                    <div>
                        <label class="mg-field-label">E-mail comercial</label>
                        <input type="email" name="company_email" class="mg-field" value="{{ old('company_email', $companyEmail) }}">
                    </div>
                    <div>
                        <label class="mg-field-label">Telefone</label>
                        <input type="text" name="company_phone" class="mg-field" value="{{ old('company_phone', $companyPhone) }}" placeholder="(00) 00000-0000">
                    </div>
                    <div>
                        <label class="mg-field-label">Cidade</label>
                        <input type="text" name="company_city" class="mg-field" value="{{ old('company_city', $companyCity) }}">
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap justify-content-end gap-2 mt-3">
                <a href="{{ route('dashboard') }}" class="mg-btn-ghost">Cancelar</a>
                <button type="submit" class="mg-btn-primary"><i class="ri-save-line"></i> Salvar alterações</button>
            </div>
        </form>

        <aside class="mg-settings-side">
            <div class="mg-settings-side-card">
                <div class="mg-panel-label mb-2">Assinatura</div>
                <p class="mb-3">Plano local ativo para operação do coach, cobrança e permissões.</p>
                <a href="{{ route('account.subscription') }}" class="mg-btn-ghost w-100 justify-content-center">
                    <i class="ri-vip-crown-line"></i> Ver assinatura
                </a>
            </div>

            <div class="mg-settings-side-card">
                <div class="mg-panel-label mb-2">Equipe</div>
                <p class="mb-3">Convide treinadores assistentes, gestores e recepção com roles Spatie.</p>
                <a href="{{ route('team.index') }}" class="mg-btn-primary w-100 justify-content-center">
                    <i class="ri-team-line"></i> Gerenciar equipe
                </a>
            </div>

            <div class="mg-settings-side-card">
                <div class="mg-panel-label mb-2">Atalhos</div>
                <div class="mg-settings-shortcuts">
                    <a href="{{ route('apps.index') }}" class="mg-settings-shortcut"><span><i class="ri-smartphone-line me-2"></i>Apps e integrações</span><i class="ri-arrow-right-s-line"></i></a>
                    <a href="{{ route('products.hub') }}" class="mg-settings-shortcut"><span><i class="ri-shopping-bag-3-line me-2"></i>Produtos</span><i class="ri-arrow-right-s-line"></i></a>
                    <a href="{{ route('settings.index') }}" class="mg-settings-shortcut"><span><i class="ri-settings-3-line me-2"></i>Identidade visual</span><i class="ri-arrow-right-s-line"></i></a>
                    <a href="{{ route('settings.index') }}#email-settings" class="mg-settings-shortcut"><span><i class="ri-mail-send-line me-2"></i>Logs de E-mail</span><i class="ri-arrow-right-s-line"></i></a>
                    <a href="{{ route('help') }}" class="mg-settings-shortcut"><span><i class="ri-question-line me-2"></i>Central de Ajuda</span><i class="ri-arrow-right-s-line"></i></a>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
