@extends('layouts.master')

@section('title', 'Perfil')

@php
    $fullName = trim($user->name ?? '');
    $nameParts = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $firstName = old('first_name', $nameParts[0] ?? '');
    $lastName = old('last_name', count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '');
    $initials = collect($nameParts)
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('') ?: 'CC';
    $uiOnlyValue = 'Nao informado localmente';
@endphp

@push('styles')
<style>
    .prime-profile-hero {
        position: relative;
        overflow: hidden;
        padding: clamp(1.25rem, 3vw, 1.75rem);
        background:
            radial-gradient(circle at top left, rgba(59, 130, 246, 0.2), transparent 34%),
            linear-gradient(135deg, rgba(17, 24, 39, 0.98), rgba(12, 16, 24, 0.98));
    }

    .prime-profile-hero__content {
        position: relative;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        z-index: 1;
    }

    .prime-profile-identity {
        display: flex;
        align-items: center;
        gap: 1rem;
        min-width: min(100%, 32rem);
    }

    .prime-profile-avatar {
        width: clamp(5.25rem, 10vw, 7rem);
        height: clamp(5.25rem, 10vw, 7rem);
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        background: linear-gradient(135deg, #2563eb, #38bdf8);
        color: #fff;
        font-size: clamp(1.7rem, 4vw, 2.35rem);
        font-weight: 800;
        letter-spacing: -0.04em;
        box-shadow: 0 22px 55px rgba(37, 99, 235, 0.32);
    }

    .prime-profile-name {
        margin: 0;
        color: var(--prime-text);
        font-size: clamp(1.55rem, 4vw, 2.35rem);
        font-weight: 800;
        letter-spacing: -0.04em;
    }

    .prime-profile-email {
        color: var(--prime-muted);
        font-weight: 600;
    }

    .prime-profile-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
        gap: 0.55rem;
    }

    .prime-profile-socials {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
    }

    .prime-profile-social {
        width: 2.15rem;
        height: 2.15rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(148, 163, 184, 0.11);
        border: 1px solid var(--prime-border-strong);
        color: var(--prime-text);
        text-decoration: none;
    }

    .prime-profile-social:hover {
        color: #fff;
        border-color: rgba(59, 130, 246, 0.55);
        background: rgba(59, 130, 246, 0.2);
    }

    .prime-profile-section-title {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        margin-bottom: 0.9rem;
        color: var(--prime-text);
        font-size: 0.95rem;
        font-weight: 700;
    }

    .prime-profile-muted-field {
        opacity: 0.72;
    }

    .prime-profile-note {
        display: flex;
        gap: 0.65rem;
        padding: 0.8rem 0.9rem;
        border: 1px solid rgba(59, 130, 246, 0.22);
        border-radius: 0.75rem;
        background: var(--prime-blue-soft);
        color: var(--prime-text);
        font-size: 0.82rem;
    }

    .prime-profile-modal .modal-content {
        background: var(--prime-surface);
        border: 1px solid var(--prime-border-strong);
        color: var(--prime-text);
    }

    .prime-profile-modal .modal-header,
    .prime-profile-modal .modal-footer {
        border-color: var(--prime-border);
    }
</style>
@endpush

@section('content')
<div class="prime-clients-page">
    <div class="prime-clients-toolbar">
        <div class="prime-clients-toolbar__left">
            <h1 class="prime-page-title mb-0">Meu perfil</h1>
            <p class="prime-page-sub mb-0">Dados da conta coach conectados ao usuario local</p>
        </div>
        <div class="prime-clients-toolbar__right">
            <a href="{{ route('settings.index') }}" class="prime-btn-primary"><i class="ri-settings-3-line"></i> Configurações</a>
        </div>
    </div>

    @if(session('message'))
        <div class="alert {{ session('alert-class', 'alert-info') }} alert-dismissible fade show mb-0" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-0" role="alert">
            Verifique os campos destacados e tente novamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <section class="prime-panel prime-profile-hero">
        <div class="prime-profile-hero__content">
            <div class="prime-profile-identity">
                <div class="prime-profile-avatar" aria-hidden="true">{{ $initials }}</div>
                <div>
                    <p class="prime-panel-label mb-1">Coach Prime</p>
                    <h2 class="prime-profile-name">{{ $fullName ?: 'Coach' }}</h2>
                    <div class="prime-profile-email">{{ $user->email }}</div>
                </div>
            </div>
            <div class="prime-profile-actions">
                <div class="prime-profile-socials" aria-label="Redes sociais">
                    <a class="prime-profile-social" href="#" title="Instagram nao conectado localmente" aria-label="Instagram"><i class="ri-instagram-line"></i></a>
                    <a class="prime-profile-social" href="#" title="WhatsApp nao conectado localmente" aria-label="WhatsApp"><i class="ri-whatsapp-line"></i></a>
                    <a class="prime-profile-social" href="#" title="YouTube nao conectado localmente" aria-label="YouTube"><i class="ri-youtube-line"></i></a>
                    <a class="prime-profile-social" href="#" title="LinkedIn nao conectado localmente" aria-label="LinkedIn"><i class="ri-linkedin-box-line"></i></a>
                </div>
                <button type="button" class="prime-btn-ghost" data-bs-toggle="modal" data-bs-target="#primePasswordModal">
                    <i class="ri-lock-password-line"></i> Alterar senha
                </button>
            </div>
        </div>
    </section>

    <form method="POST" action="{{ route('updateProfile', $user->id) }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="email" value="{{ old('email', $user->email) }}">

        <div class="row g-3">
            <div class="col-xl-6">
                <section class="prime-panel">
                    <div class="prime-profile-section-title">
                        <i class="ri-user-3-line text-primary"></i>
                        Informações pessoais
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="profileFirstName" class="prime-field-label">Nome</label>
                            <input id="profileFirstName" type="text" name="first_name" value="{{ $firstName }}" class="prime-field {{ isset($errors) && $errors->has('first_name') ? 'is-invalid' : '' }}" autocomplete="given-name">
                            @if(isset($errors) && $errors->has('first_name'))
                                <div class="invalid-feedback d-block">{{ $errors->first('first_name') }}</div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label for="profileLastName" class="prime-field-label">Sobrenome</label>
                            <input id="profileLastName" type="text" name="last_name" value="{{ $lastName }}" class="prime-field {{ isset($errors) && $errors->has('last_name') ? 'is-invalid' : '' }}" autocomplete="family-name">
                            @if(isset($errors) && $errors->has('last_name'))
                                <div class="invalid-feedback d-block">{{ $errors->first('last_name') }}</div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label for="profileBirthDate" class="prime-field-label">Data nascimento</label>
                            <input id="profileBirthDate" type="text" value="{{ $uiOnlyValue }}" class="prime-field prime-profile-muted-field" disabled title="Campo visual: users nao possui data de nascimento">
                        </div>
                        <div class="col-md-6">
                            <label for="profileGender" class="prime-field-label">Sexo</label>
                            <select id="profileGender" class="prime-field prime-profile-muted-field" disabled title="Campo visual: users nao possui sexo">
                                <option>{{ $uiOnlyValue }}</option>
                            </select>
                        </div>
                    </div>
                </section>
            </div>

            <div class="col-xl-6">
                <section class="prime-panel">
                    <div class="prime-profile-section-title">
                        <i class="ri-map-pin-line text-primary"></i>
                        Endereço
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="prime-field-label">CEP</label>
                            <input type="text" value="{{ $uiOnlyValue }}" class="prime-field prime-profile-muted-field" disabled title="Campo visual: users nao possui CEP">
                        </div>
                        <div class="col-md-8">
                            <label class="prime-field-label">Rua</label>
                            <input type="text" value="{{ $uiOnlyValue }}" class="prime-field prime-profile-muted-field" disabled title="Campo visual: users nao possui rua">
                        </div>
                        <div class="col-md-4">
                            <label class="prime-field-label">Número</label>
                            <input type="text" value="{{ $uiOnlyValue }}" class="prime-field prime-profile-muted-field" disabled title="Campo visual: users nao possui numero">
                        </div>
                        <div class="col-md-8">
                            <label class="prime-field-label">Complemento</label>
                            <input type="text" value="{{ $uiOnlyValue }}" class="prime-field prime-profile-muted-field" disabled title="Campo visual: users nao possui complemento">
                        </div>
                        <div class="col-md-6">
                            <label class="prime-field-label">Bairro</label>
                            <input type="text" value="{{ $uiOnlyValue }}" class="prime-field prime-profile-muted-field" disabled title="Campo visual: users nao possui bairro">
                        </div>
                        <div class="col-md-6">
                            <label class="prime-field-label">Cidade</label>
                            <input type="text" value="{{ $uiOnlyValue }}" class="prime-field prime-profile-muted-field" disabled title="Campo visual: users nao possui cidade">
                        </div>
                        <div class="col-md-4">
                            <label class="prime-field-label">Estado</label>
                            <input type="text" value="{{ $uiOnlyValue }}" class="prime-field prime-profile-muted-field" disabled title="Campo visual: users nao possui estado">
                        </div>
                        <div class="col-md-8">
                            <label class="prime-field-label">País</label>
                            <input type="text" value="{{ $uiOnlyValue }}" class="prime-field prime-profile-muted-field" disabled title="Campo visual: users nao possui pais">
                        </div>
                    </div>

                    <div class="prime-profile-note mt-3">
                        <i class="ri-information-line fs-5"></i>
                        <div>
                            Complete estes dados quando o cadastro local suportar endereço. Eles ajudam na elegibilidade e comunicados de premiações do coach.
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <div class="prime-panel prime-panel--compact d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <div class="prime-panel-label mb-1">Conta local</div>
                <div class="prime-panel-hint mt-0">E-mail atual: {{ $user->email }}</div>
            </div>
            <button type="submit" class="prime-btn-primary">
                <i class="ri-save-3-line"></i> Salvar alterações
            </button>
        </div>
    </form>

    <div class="modal fade prime-profile-modal" id="primePasswordModal" tabindex="-1" aria-labelledby="primePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="primePasswordForm" class="modal-content" method="POST" action="{{ route('updatePassword', $user->id) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="primePasswordModalLabel">Alterar senha</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div id="primePasswordFeedback" class="alert d-none mb-3" role="alert"></div>
                    <div class="mb-3">
                        <label for="currentPassword" class="prime-field-label">Senha atual</label>
                        <input id="currentPassword" type="password" name="current_password" class="prime-field" autocomplete="current-password" required>
                    </div>
                    <div class="mb-3">
                        <label for="newPassword" class="prime-field-label">Nova senha</label>
                        <input id="newPassword" type="password" name="password" class="prime-field" autocomplete="new-password" required>
                    </div>
                    <div>
                        <label for="newPasswordConfirmation" class="prime-field-label">Confirmar nova senha</label>
                        <input id="newPasswordConfirmation" type="password" name="password_confirmation" class="prime-field" autocomplete="new-password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="prime-btn-ghost" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="prime-btn-primary">
                        <i class="ri-check-line"></i> Atualizar senha
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('primePasswordForm');
        const feedback = document.getElementById('primePasswordFeedback');

        if (!form || !feedback) {
            return;
        }

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            feedback.className = 'alert alert-info mb-3';
            feedback.textContent = 'Atualizando senha...';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const data = await response.json().catch(() => ({}));

                if (response.ok && data.isSuccess) {
                    feedback.className = 'alert alert-success mb-3';
                    feedback.textContent = data.Message || 'Senha atualizada com sucesso.';
                    form.reset();
                    return;
                }

                feedback.className = 'alert alert-danger mb-3';
                feedback.textContent = data.Message || 'Nao foi possivel atualizar a senha. Verifique os dados informados.';
            } catch (error) {
                feedback.className = 'alert alert-danger mb-3';
                feedback.textContent = 'Nao foi possivel atualizar a senha agora.';
            }
        });
    });
</script>
@endsection
