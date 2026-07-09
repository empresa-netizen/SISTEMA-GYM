@extends('prime.layouts.auth')

@section('title', 'Verificar e-mail')

@section('content')
<div class="prime-auth-split">
    <div class="prime-auth-brand"><span class="prime-logo-mark">P</span></div>
    <div class="prime-auth-form-wrap">
        <div class="prime-form-card">
            <div class="prime-form-icon"><i class="ri-mail-check-line"></i></div>
            <h1>Verifique seu e-mail</h1>
            <p class="prime-subtitle">Enviamos um link de confirmação para {{ auth()->user()->email ?? 'seu e-mail' }}.</p>

            @if (session('status'))
                <div class="prime-alert" style="background:rgba(34,197,94,.12);border-color:rgba(34,197,94,.25);color:#bbf7d0;">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('email.verification.send') }}">
                @csrf
                <button type="submit" class="btn btn-primary w-100">Reenviar e-mail</button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="text-center mt-3">
                @csrf
                <button type="submit" class="btn btn-link btn-sm text-muted">Sair</button>
            </form>
        </div>
    </div>
</div>
@endsection
