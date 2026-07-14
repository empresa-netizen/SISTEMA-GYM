@extends('mgteam.layouts.auth')

@section('title', 'Login')

@section('content')
<div class="mg-auth-split">
    <div class="mg-auth-brand">
        <div class="mg-auth-brand-inner">
            <img
                src="{{ asset('brand/mgteam-branco.svg') }}"
                alt="{{ config('brand.name') }}"
                class="mg-auth-brand-logo"
            >
            <p class="mg-auth-slogan">
                Acompanhamento que trata você
                <em>por inteiro.</em>
            </p>
            <p class="mg-auth-handle">{{ config('brand.handle', '@mgteamoficial') }}</p>
        </div>
    </div>

    <div class="mg-auth-form-wrap">
        <div class="mg-form-card">
            <div class="mg-form-brand-mobile">
                @include('mgteam.partials.logo', ['size' => 'sm', 'variant' => 'dark'])
            </div>

            <div class="mg-form-icon"><i class="ri-key-2-line"></i></div>
            <h1>Acesse a {{ config('brand.short', 'MGTEAM') }}</h1>
            <p class="mg-subtitle">Entre com suas credenciais de profissional</p>

            @if ($errors->any())
                <div class="mg-alert">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @if (session('status'))
                <div class="mg-alert mg-alert--success">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mg-field">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="seu@email.com" required autofocus>
                </div>

                <div class="mg-field">
                    <label for="password-input">Senha</label>
                    <div class="mg-password-wrap">
                        <input type="password" id="password-input" name="password" placeholder="digite sua senha" required>
                        <button type="button" id="password-toggle" aria-label="Mostrar senha"><i class="ri-eye-line"></i></button>
                    </div>
                </div>

                <div class="mg-link-row">
                    <label style="display:flex;align-items:center;gap:.4rem;color:var(--mg-muted);">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        Lembrar-me
                    </label>
                    <a href="{{ route('password.request') }}">Esqueci minha senha</a>
                </div>

                <button type="submit" class="mg-btn">Entrar</button>
            </form>

            <p class="mg-footer-link">
                Não tem uma conta? <a href="{{ route('register') }}">Cadastre-se</a>
            </p>
            <p class="mg-footer-link">
                <a href="{{ route('welcome') }}">Voltar para a página inicial</a>
            </p>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.getElementById('password-toggle')?.addEventListener('click', function () {
    const input = document.getElementById('password-input');
    const icon = this.querySelector('i');
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    icon.className = isPassword ? 'ri-eye-off-line' : 'ri-eye-line';
});
</script>
@endsection
