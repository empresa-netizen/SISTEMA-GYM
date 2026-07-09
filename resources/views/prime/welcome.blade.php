@extends('prime.layouts.auth')

@section('title', 'Bem-vindo')

@section('content')
<div class="prime-shell">
    <div class="prime-center">
        @include('prime.partials.logo')

        <div id="prime-home">
            <h1 class="prime-title">Bem-vindo à {{ config('brand.short', 'MGTEAM') }}</h1>
            <p class="prime-subtitle">Selecione como deseja acessar a plataforma</p>

            <button type="button" class="prime-choice" data-target="professional">
                <div class="prime-choice-inner">
                    <span class="prime-choice-icon"><i class="ri-briefcase-line"></i></span>
                    <div>
                        <h2>Sou Profissional</h2>
                        <p>Personal, nutricionista ou treinador que prescreve e gerencia clientes</p>
                    </div>
                    <span class="prime-chevron"><i class="ri-arrow-right-s-line"></i></span>
                </div>
            </button>

            <button type="button" class="prime-choice" data-target="client">
                <div class="prime-choice-inner">
                    <span class="prime-choice-icon"><i class="ri-user-line"></i></span>
                    <div>
                        <h2>Sou Cliente</h2>
                        <p>Aluno acompanhado por um profissional {{ config('brand.short', 'MGTEAM') }}</p>
                    </div>
                    <span class="prime-chevron"><i class="ri-arrow-right-s-line"></i></span>
                </div>
            </button>
        </div>

        <div id="prime-professional" class="prime-panel">
            <button type="button" class="prime-back" data-target="home"><i class="ri-arrow-left-line"></i> Voltar</button>
            <h1 class="prime-title" style="text-align:left;font-size:1.5rem;">Sou Profissional</h1>
            <p class="prime-subtitle" style="text-align:left;">Personal, nutricionista ou treinador que prescreve e gerencia clientes</p>

            <div class="prime-panel-card">
                <div class="prime-panel-inner">
                    <span class="prime-choice-icon"><i class="ri-computer-line"></i></span>
                    <div>
                        <h3>Acesso Web</h3>
                        <p>Onde você prescreve treinos e dietas, gerencia clientes, vendas e toda a sua operação.</p>
                    </div>
                </div>
                <a href="{{ route('login') }}" class="prime-btn">Entrar no painel</a>
                <a href="{{ route('register') }}" class="prime-btn prime-btn-outline" style="margin-top:0.65rem;">Criar conta grátis</a>
            </div>

            <div class="prime-panel-card">
                <div class="prime-panel-inner">
                    <span class="prime-choice-icon"><i class="ri-smartphone-line"></i></span>
                    <div>
                        <h3>App do Profissional</h3>
                        <p>Acompanhe métricas e vendas, converse com clientes e interaja no feed.</p>
                    </div>
                </div>
                <a href="http://localhost:8089" target="_blank" rel="noopener" class="prime-btn">Abrir app</a>
            </div>
        </div>

        <div id="prime-client" class="prime-panel">
            <button type="button" class="prime-back" data-target="home"><i class="ri-arrow-left-line"></i> Voltar</button>
            <h1 class="prime-title" style="text-align:left;font-size:1.5rem;">Sou Cliente</h1>
            <p class="prime-subtitle" style="text-align:left;">Aluno acompanhado por um profissional {{ config('brand.short', 'MGTEAM') }}</p>

            <div class="prime-panel-card">
                <div class="prime-panel-inner">
                    <span class="prime-choice-icon"><i class="ri-smartphone-line"></i></span>
                    <div>
                        <h3>App do Aluno</h3>
                        <p>Treinos, dieta, chat com o coach, diário e evolução no celular.</p>
                    </div>
                </div>
                <a href="http://localhost:8086" target="_blank" rel="noopener" class="prime-btn">Abrir app</a>
            </div>

            <div class="prime-panel-card">
                <div class="prime-panel-inner">
                    <span class="prime-choice-icon"><i class="ri-computer-line"></i></span>
                    <div>
                        <h3>Área do Cliente (web)</h3>
                        <p>Veja treinos, dietas e fale com seu profissional pelo painel web.</p>
                    </div>
                </div>
                <a href="{{ route('login') }}" class="prime-btn prime-btn-outline">Entrar como cliente</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.querySelectorAll('[data-target]').forEach((el) => {
    el.addEventListener('click', () => {
        const target = el.dataset.target;
        document.getElementById('prime-home').style.display = target === 'home' ? 'block' : 'none';
        document.getElementById('prime-professional').classList.toggle('is-active', target === 'professional');
        document.getElementById('prime-client').classList.toggle('is-active', target === 'client');
        if (target === 'professional' || target === 'client') {
            document.getElementById('prime-home').style.display = 'none';
        }
    });
});
</script>
@endsection
