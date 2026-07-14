@extends('prime.layouts.auth')

@section('title', 'Login')

@section('content')
<div class="prime-auth-split">
    <div class="prime-auth-brand">
        <div class="prime-auth-brand-inner">
            <img
                src="{{ asset('brand/mgteam-branco.svg') }}"
                alt="{{ config('brand.name') }}"
                class="prime-auth-brand-logo"
            >
            <p class="prime-auth-slogan">
                Acompanhamento que trata você
                <em>por inteiro.</em>
            </p>
            <p class="prime-auth-handle">{{ config('brand.handle', '@mgteamoficial') }}</p>
        </div>
    </div>

    <div class="prime-auth-form-wrap">
        <div class="prime-form-card">
            <div class="prime-form-brand-mobile">
                @include('prime.partials.logo', ['size' => 'sm', 'variant' => 'dark'])
            </div>

            <div class="prime-form-icon"><i class="ri-key-2-line"></i></div>
            <h1>Acesse a {{ config('brand.short', 'MGTEAM') }}</h1>
            <p class="prime-subtitle">Entre com suas credenciais de profissional</p>

            @if ($errors->any())
                <div class="prime-alert">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @if (session('status'))
                <div class="prime-alert prime-alert--success">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="prime-field">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="seu@email.com" required autofocus>
                </div>

                <div class="prime-field">
                    <label for="password-input">Senha</label>
                    <div class="prime-password-wrap">
                        <input type="password" id="password-input" name="password" placeholder="digite sua senha" required>
                        <button type="button" id="password-toggle" aria-label="Mostrar senha"><i class="ri-eye-line"></i></button>
                    </div>
                </div>

                <div class="prime-link-row">
                    <label style="display:flex;align-items:center;gap:.4rem;color:var(--prime-muted);">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        Lembrar-me
                    </label>
                    <a href="{{ route('password.request') }}">Esqueci minha senha</a>
                </div>

                <button type="submit" class="prime-btn">Entrar</button>
            </form>

            <p class="prime-footer-link">
                Não tem uma conta? <a href="{{ route('register') }}">Cadastre-se</a>
            </p>
            <p class="prime-footer-link">
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
