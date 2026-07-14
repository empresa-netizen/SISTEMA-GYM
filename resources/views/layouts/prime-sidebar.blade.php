@php
    $initials = collect(explode(' ', auth()->user()->name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
@endphp
<aside class="prime-rail" id="primeRail">
    <div class="prime-rail-brand-wrap">
        <div class="prime-rail-brand" aria-label="{{ config('brand.name', 'MGTEAM FITNESS & HEALTH') }}">
            @include('prime.partials.logo', ['size' => 'sm', 'variant' => 'dark'])
        </div>
    </div>

    <button type="button" class="prime-rail-toggle" id="primeRailToggle" title="Expandir/Recolher menu" aria-label="Expandir/Recolher menu">
        <i class="ri-arrow-right-s-line"></i>
    </button>

    <nav class="prime-rail-nav">
        <a href="{{ route('dashboard') }}" class="prime-rail-item @if(Route::is('dashboard')) is-active @endif" title="Resumo">
            <i class="ri-home-4-line"></i><span>Resumo</span>
        </a>
        <a href="{{ route('events.schedule') }}" class="prime-rail-item @if(Route::is('events.*')) is-active @endif" title="Agenda">
            <i class="ri-calendar-line"></i><span>Agenda</span>
        </a>

        @can('view plans')
        <div class="prime-rail-group @if(Route::is('products.*', 'membership-plans.*')) is-active @endif">
            <button type="button" class="prime-rail-item prime-rail-trigger" title="Produtos">
                <i class="ri-price-tag-3-line"></i><span>Produtos</span><i class="ri-arrow-right-s-line prime-rail-chevron"></i>
            </button>
            <div class="prime-rail-flyout">
                <p class="prime-rail-flyout-title">Produtos</p>
                <a href="{{ route('products.hub') }}" class="prime-rail-flyout-link @if(Route::is('products.hub')) is-active @endif">Vitrine</a>
                <a href="{{ route('membership-plans.index') }}" class="prime-rail-flyout-link @if(Route::is('membership-plans.*')) is-active @endif">Meus produtos</a>
                <a href="{{ route('events.index') }}" class="prime-rail-flyout-link @if(Route::is('events.*')) is-active @endif">Eventos <span class="prime-rail-badge">Novo</span></a>
                <a href="{{ route('products.coupons') }}" class="prime-rail-flyout-link @if(Route::is('products.coupons')) is-active @endif">Cupons</a>
                <a href="{{ route('products.affiliates') }}" class="prime-rail-flyout-link @if(Route::is('products.affiliates')) is-active @endif">Afiliados</a>
                <a href="{{ route('products.cart-recovery') }}" class="prime-rail-flyout-link @if(Route::is('products.cart-recovery')) is-active @endif">Recuperação de carrinho</a>
            </div>
        </div>
        @endcan

        @can('view members')
        <div class="prime-rail-group @if(Route::is('members.*', 'feedbacks.*', 'messages.*', 'prescriptions.*')) is-active is-open @endif">
            <button type="button" class="prime-rail-item prime-rail-trigger" title="Clientes">
                <i class="ri-group-line"></i><span>Clientes</span><i class="ri-arrow-right-s-line prime-rail-chevron"></i>
            </button>
            <div class="prime-rail-flyout">
                <p class="prime-rail-flyout-title">Clientes</p>
                <a href="{{ route('members.index') }}" class="prime-rail-flyout-link @if(Route::is('members.index', 'members.show', 'members.create', 'members.edit')) is-active @endif">Ativos</a>
                <a href="{{ route('feedbacks.index') }}" class="prime-rail-flyout-link @if(Route::is('feedbacks.*')) is-active @endif">Feedbacks</a>
                <a href="{{ route('members.logbook') }}" class="prime-rail-flyout-link @if(Route::is('members.logbook')) is-active @endif">Diário de registros</a>
                <a href="{{ route('messages.index') }}" class="prime-rail-flyout-link @if(Route::is('messages.*')) is-active @endif">Mensagens</a>
                <a href="{{ route('members.all') }}" class="prime-rail-flyout-link @if(Route::is('members.all')) is-active @endif">Todos os clientes</a>
                <a href="{{ route('prescriptions.index') }}" class="prime-rail-flyout-link @if(Route::is('prescriptions.*')) is-active @endif">Prescrições Agendadas</a>
                <a href="{{ route('members.renewals') }}" class="prime-rail-flyout-link @if(Route::is('members.renewals')) is-active @endif">Estimativa de Renovações</a>
                <a href="{{ route('members.engagement') }}" class="prime-rail-flyout-link @if(Route::is('members.engagement')) is-active @endif">Engajamento</a>
                <a href="{{ route('members.dropouts') }}" class="prime-rail-flyout-link @if(Route::is('members.dropouts')) is-active @endif">Desistências</a>
                <a href="{{ route('members.groups') }}" class="prime-rail-flyout-link @if(Route::is('members.groups')) is-active @endif">Grupos</a>
                <a href="{{ route('members.attendances') }}" class="prime-rail-flyout-link @if(Route::is('members.attendances')) is-active @endif">Atendimentos</a>
                <a href="{{ route('members.pending') }}" class="prime-rail-flyout-link @if(Route::is('members.pending')) is-active @endif">Pendências de treino</a>
                <a href="{{ route('tools.import.customers') }}" class="prime-rail-flyout-link @if(Route::is('tools.import.customers*')) is-active @endif">Importar clientes</a>
            </div>
        </div>
        @endcan

        <div class="prime-rail-group @if(Route::is('library.*', 'exercises.*', 'workouts.*', 'workout-templates.*', 'prescriptions.*')) is-active @endif">
            <button type="button" class="prime-rail-item prime-rail-trigger" title="Bibliotecas">
                <i class="ri-book-open-line"></i><span>Bibliotecas</span><i class="ri-arrow-right-s-line prime-rail-chevron"></i>
            </button>
            <div class="prime-rail-flyout">
                <p class="prime-rail-flyout-title">Bibliotecas</p>
                <a href="{{ route('library.hub') }}" class="prime-rail-flyout-link @if(Route::is('library.hub')) is-active @endif">Hub</a>
                <a href="{{ route('exercises.index') }}" class="prime-rail-flyout-link @if(Route::is('exercises.*')) is-active @endif">Exercícios</a>
                <a href="{{ route('library.workout') }}" class="prime-rail-flyout-link @if(Route::is('library.workout', 'workouts.*', 'workout-templates.*')) is-active @endif">Treino</a>
                <a href="{{ route('workout-templates.index') }}" class="prime-rail-flyout-link @if(Route::is('workout-templates.*')) is-active @endif">Templates de treino</a>
                <a href="{{ route('library.diet.index') }}" class="prime-rail-flyout-link @if(Route::is('library.diet.*')) is-active @endif">Dieta</a>
                <a href="{{ route('library.courses') }}" class="prime-rail-flyout-link @if(Route::is('library.courses*')) is-active @endif">Cursos</a>
                <a href="{{ route('prescriptions.index') }}" class="prime-rail-flyout-link @if(Route::is('prescriptions.*')) is-active @endif">Prescrições</a>
            </div>
        </div>

        <div class="prime-rail-group @if(Route::is('tools.*', 'feed.*', 'community.*', 'healths.*')) is-active @endif">
            <button type="button" class="prime-rail-item prime-rail-trigger" title="Ferramentas">
                <i class="ri-tools-line"></i><span>Ferramentas</span><i class="ri-arrow-right-s-line prime-rail-chevron"></i>
            </button>
            <div class="prime-rail-flyout">
                <p class="prime-rail-flyout-title">Ferramentas</p>
                <a href="{{ route('tools.anamnesis') }}" class="prime-rail-flyout-link @if(Route::is('tools.anamnesis')) is-active @endif">Anamnese</a>
                <a href="{{ route('prescriptions.index') }}" class="prime-rail-flyout-link @if(Route::is('prescriptions.*')) is-active @endif">Prescrições</a>
                <a href="{{ route('events.index') }}" class="prime-rail-flyout-link @if(Route::is('events.*')) is-active @endif">Eventos</a>
                <a href="{{ route('healths.index') }}" class="prime-rail-flyout-link @if(Route::is('healths.*')) is-active @endif">Evolução</a>
            </div>
        </div>

        @can('view payments')
        <a href="{{ route('finance.index') }}" class="prime-rail-item @if(Route::is('finance.*', 'invoices.*')) is-active @endif" title="Financeiro">
            <i class="ri-wallet-3-line"></i><span>Financeiro</span>
        </a>
        @endcan

        <a href="{{ route('apps.index') }}" class="prime-rail-item @if(Route::is('apps.*')) is-active @endif" title="Apps">
            <i class="ri-smartphone-line"></i><span>Apps</span>
        </a>
        <a href="{{ route('feed.index') }}" class="prime-rail-item @if(Route::is('feed.*')) is-active @endif" title="Feed">
            <i class="ri-rss-line"></i><span>Feed</span>
        </a>
        <a href="{{ route('community.index') }}" class="prime-rail-item @if(Route::is('community.*')) is-active @endif" title="Comunidade">
            <i class="ri-community-line"></i><span>Comunidade</span>
        </a>
        <a href="{{ route('help') }}" class="prime-rail-item @if(Route::is('help', 'support-tickets.*')) is-active @endif" title="Suporte">
            <i class="ri-question-line"></i><span>Suporte</span>
        </a>
    </nav>

    <div class="prime-rail-footer">
        <a href="{{ route('reports.index') }}" class="prime-rail-item @if(Route::is('reports.*')) is-active @endif" title="Minha conta">
            <i class="ri-user-3-line"></i><span>Minha conta</span>
        </a>
        @can('view users')
        <a href="{{ route('team.index') }}" class="prime-rail-item @if(Route::is('team.*', 'account.collaborators', 'users.*')) is-active @endif" title="Meus colaboradores">
            <i class="ri-team-line"></i><span>Colaboradores</span>
        </a>
        @endcan
        <a href="{{ route('account.settings') }}" class="prime-rail-item @if(Route::is('account.settings', 'account.profile.*', 'settings.*')) is-active @endif" title="Configurações">
            <i class="ri-settings-3-line"></i><span>Configurações</span>
        </a>
    </div>
</aside>
