@extends('mgteam.layouts.auth')

@section('title', 'Cadastro')

@section('content')
<div class="mg-shell">
    <div class="mg-form-card" style="max-width:28rem;margin:0 auto;">
        @include('mgteam.partials.logo', ['size' => 'md', 'variant' => 'dark'])

        <h1 class="mg-title" style="font-size:1.85rem;">Aproveite a avaliação gratuita</h1>
        <p class="mg-subtitle">Crie sua conta profissional e comece a gerenciar clientes, treinos e vendas.</p>

        @if ($errors->any())
            <div class="mg-alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
            @csrf

            <div class="mg-field">
                <label for="name">Nome completo</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Digite seu nome completo" required>
            </div>

            <div class="mg-field">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="seu@email.com" required>
            </div>

            <div class="mg-field">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" placeholder="Crie uma senha segura" required>
            </div>

            <div class="mg-field">
                <label for="password_confirmation">Confirmar senha</label>
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Repita sua senha" required>
            </div>

            <button type="submit" class="mg-btn">Crie uma conta na MGTEAM</button>
        </form>

        <p class="mg-footer-link">
            Já tem uma conta? <a href="{{ route('login') }}">Fazer login</a>
        </p>
        <p class="mg-footer-link">
            <a href="{{ route('welcome') }}">Voltar para a página inicial</a>
        </p>
    </div>
</div>
@endsection
