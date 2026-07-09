@extends('prime.layouts.auth')

@section('title', 'Verificação em duas etapas')

@section('content')
<div class="prime-auth-split">
    <div class="prime-auth-brand"><span class="prime-logo-mark">P</span></div>
    <div class="prime-auth-form-wrap">
        <div class="prime-form-card">
            <div class="prime-form-icon"><i class="ri-shield-keyhole-line"></i></div>
            <h1>Verificação em duas etapas</h1>
            <p class="prime-subtitle">Digite o código de 6 dígitos do seu autenticador</p>

            @if ($errors->any())
                <div class="prime-alert">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login.2fa.verify') }}">
                @csrf
                <div class="mb-3">
                    <label for="code" class="form-label">Código</label>
                    <input type="text" class="form-control form-control-lg text-center @error('code') is-invalid @enderror"
                           id="code" name="code" placeholder="000000" maxlength="6" required autofocus pattern="[0-9]{6}">
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button class="btn btn-primary w-100" type="submit">Verificar</button>
            </form>

            <p class="text-center mt-3 mb-0 small text-muted">
                Perdeu o dispositivo? <a href="{{ route('login') }}">Voltar ao login</a>
            </p>
            <form method="POST" action="{{ route('logout') }}" class="text-center mt-2">
                @csrf
                <button type="submit" class="btn btn-link btn-sm text-muted">Sair</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.getElementById('code')?.addEventListener('input', function () {
    if (this.value.length === 6) this.form.submit();
});
</script>
@endsection
