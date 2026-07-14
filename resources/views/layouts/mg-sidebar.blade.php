@php
    $initials = collect(explode(' ', auth()->user()->name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
@endphp
<aside class="mg-rail" id="mgRail">
    <div class="mg-rail-brand-wrap">
        <div class="mg-rail-brand" aria-label="{{ config('brand.name', 'MGTEAM FITNESS & HEALTH') }}">
            @include('mgteam.partials.logo', ['size' => 'sm', 'variant' => 'dark'])
        </div>
    </div>

    <button type="button" class="mg-rail-toggle" id="mgRailToggle" title="Expandir/Recolher menu" aria-label="Expandir/Recolher menu">
        <i class="ri-arrow-right-s-line"></i>
    </button>

    <nav class="mg-rail-nav">
        <a href="{{ route('dashboard') }}" class="mg-rail-item @if(Route::is('dashboard')) is-active @endif" title="Resumo">
            <i class="ri-home-4-line"></i><span>Resumo</span>
        </a>
        <a href="{{ route('events.schedule') }}" class="mg-rail-item @if(Route::is('events.*')) is-active @endif" title="Agenda">
            <i class="ri-calendar-line"></i><span>Agenda</span>
        </a>

        @can('view plans')
        <div class="mg-rail-group @if(Route::is('products.*', 'membership-plans.*')) is-active @endif">
            <button type="button" class="mg-rail-item mg-rail-trigger" title="Produtos">
                <i class="ri-price-tag-3-line"></i><span>Produtos</span><i class="ri-arrow-right-s-line mg-rail-chevron"></i>
            </button>
            <div class="mg-rail-flyout">
                <p class="mg-rail-flyout-title">Produtos</p>
                <a href="{{ route('products.hub') }}" class="mg-rail-flyout-link @if(Route::is('products.hub')) is-active @endif">Vitrine</a>
                <a href="{{ route('membership-plans.index') }}" class="mg-rail-flyout-link @if(Route::is('membership-plans.*')) is-active @endif">Meus produtos</a>
                <a href="{{ route('events.index') }}" class="mg-rail-flyout-link @if(Route::is('events.*')) is-active @endif">Eventos <span class="mg-rail-badge">Novo</span></a>
                <a href="{{ route('products.coupons') }}" class="mg-rail-flyout-link @if(Route::is('products.coupons')) is-active @endif">Cupons</a>
                <a href="{{ route('products.affiliates') }}" class="mg-rail-flyout-link @if(Route::is('products.affiliates')) is-active @endif">Afiliados</a>
                <a href="{{ route('products.cart-recovery') }}" class="mg-rail-flyout-link @if(Route::is('products.cart-recovery')) is-active @endif">Recuperação de carrinho</a>
            </div>
        </div>
        @endcan

        @can('view members')
        <div class="mg-rail-group @if(Route::is('members.*', 'feedbacks.*', 'messages.*', 'prescriptions.*')) is-active is-open @endif">
            <button type="button" class="mg-rail-item mg-rail-trigger" title="Clientes">
                <i class="ri-group-line"></i><span>Clientes</span><i class="ri-arrow-right-s-line mg-rail-chevron"></i>
            </button>
            <div class="mg-rail-flyout">
                <p class="mg-rail-flyout-title">Clientes</p>
                <a href="{{ route('members.index') }}" class="mg-rail-flyout-link @if(Route::is('members.index', 'members.show', 'members.create', 'members.edit')) is-active @endif">Ativos</a>
                <a href="{{ route('feedbacks.index') }}" class="mg-rail-flyout-link @if(Route::is('feedbacks.*')) is-active @endif">Feedbacks</a>
                <a href="{{ route('members.logbook') }}" class="mg-rail-flyout-link @if(Route::is('members.logbook')) is-active @endif">Diário de registros</a>
                <a href="{{ route('messages.index') }}" class="mg-rail-flyout-link @if(Route::is('messages.*')) is-active @endif">Mensagens</a>
                <a href="{{ route('members.all') }}" class="mg-rail-flyout-link @if(Route::is('members.all')) is-active @endif">Todos os clientes</a>
                <a href="{{ route('prescriptions.index') }}" class="mg-rail-flyout-link @if(Route::is('prescriptions.*')) is-active @endif">Prescrições Agendadas</a>
                <a href="{{ route('members.renewals') }}" class="mg-rail-flyout-link @if(Route::is('members.renewals')) is-active @endif">Estimativa de Renovações</a>
                <a href="{{ route('members.engagement') }}" class="mg-rail-flyout-link @if(Route::is('members.engagement')) is-active @endif">Engajamento</a>
                <a href="{{ route('members.dropouts') }}" class="mg-rail-flyout-link @if(Route::is('members.dropouts')) is-active @endif">Desistências</a>
                <a href="{{ route('members.groups') }}" class="mg-rail-flyout-link @if(Route::is('members.groups')) is-active @endif">Grupos</a>
                <a href="{{ route('members.attendances') }}" class="mg-rail-flyout-link @if(Route::is('members.attendances')) is-active @endif">Atendimentos</a>
                <a href="{{ route('members.pending') }}" class="mg-rail-flyout-link @if(Route::is('members.pending')) is-active @endif">Pendências de treino</a>
                <a href="{{ route('tools.import.customers') }}" class="mg-rail-flyout-link @if(Route::is('tools.import.customers*')) is-active @endif">Importar clientes</a>
            </div>
        </div>
        @endcan

        <div class="mg-rail-group @if(Route::is('library.*', 'exercises.*', 'workouts.*', 'workout-templates.*', 'prescriptions.*')) is-active @endif">
            <button type="button" class="mg-rail-item mg-rail-trigger" title="Bibliotecas">
                <i class="ri-book-open-line"></i><span>Bibliotecas</span><i class="ri-arrow-right-s-line mg-rail-chevron"></i>
            </button>
            <div class="mg-rail-flyout">
                <p class="mg-rail-flyout-title">Bibliotecas</p>
                <a href="{{ route('library.hub') }}" class="mg-rail-flyout-link @if(Route::is('library.hub')) is-active @endif">Hub</a>
                <a href="{{ route('exercises.index') }}" class="mg-rail-flyout-link @if(Route::is('exercises.*')) is-active @endif">Exercícios</a>
                <a href="{{ route('library.workout') }}" class="mg-rail-flyout-link @if(Route::is('library.workout', 'workouts.*', 'workout-templates.*')) is-active @endif">Treino</a>
                <a href="{{ route('workout-templates.index') }}" class="mg-rail-flyout-link @if(Route::is('workout-templates.*')) is-active @endif">Templates de treino</a>
                <a href="{{ route('library.diet.index') }}" class="mg-rail-flyout-link @if(Route::is('library.diet.*')) is-active @endif">Dieta</a>
                <a href="{{ route('library.courses') }}" class="mg-rail-flyout-link @if(Route::is('library.courses*')) is-active @endif">Cursos</a>
                <a href="{{ route('prescriptions.index') }}" class="mg-rail-flyout-link @if(Route::is('prescriptions.*')) is-active @endif">Prescrições</a>
            </div>
        </div>

        <div class="mg-rail-group @if(Route::is('tools.*', 'feed.*', 'community.*', 'healths.*')) is-active @endif">
            <button type="button" class="mg-rail-item mg-rail-trigger" title="Ferramentas">
                <i class="ri-tools-line"></i><span>Ferramentas</span><i class="ri-arrow-right-s-line mg-rail-chevron"></i>
            </button>
            <div class="mg-rail-flyout">
                <p class="mg-rail-flyout-title">Ferramentas</p>
                <a href="{{ route('tools.anamnesis') }}" class="mg-rail-flyout-link @if(Route::is('tools.anamnesis')) is-active @endif">Anamnese</a>
                <a href="{{ route('prescriptions.index') }}" class="mg-rail-flyout-link @if(Route::is('prescriptions.*')) is-active @endif">Prescrições</a>
                <a href="{{ route('events.index') }}" class="mg-rail-flyout-link @if(Route::is('events.*')) is-active @endif">Eventos</a>
                <a href="{{ route('healths.index') }}" class="mg-rail-flyout-link @if(Route::is('healths.*')) is-active @endif">Evolução</a>
            </div>
        </div>

        @can('view payments')
        <a href="{{ route('finance.index') }}" class="mg-rail-item @if(Route::is('finance.*', 'invoices.*')) is-active @endif" title="Financeiro">
            <i class="ri-wallet-3-line"></i><span>Financeiro</span>
        </a>
        @endcan

        <a href="{{ route('apps.index') }}" class="mg-rail-item @if(Route::is('apps.*')) is-active @endif" title="Apps">
            <i class="ri-smartphone-line"></i><span>Apps</span>
        </a>
        <a href="{{ route('feed.index') }}" class="mg-rail-item @if(Route::is('feed.*')) is-active @endif" title="Feed">
            <i class="ri-rss-line"></i><span>Feed</span>
        </a>
        <a href="{{ route('community.index') }}" class="mg-rail-item @if(Route::is('community.*')) is-active @endif" title="Comunidade">
            <i class="ri-community-line"></i><span>Comunidade</span>
        </a>
        <a href="{{ route('help') }}" class="mg-rail-item @if(Route::is('help', 'support-tickets.*')) is-active @endif" title="Suporte">
            <i class="ri-question-line"></i><span>Suporte</span>
        </a>
    </nav>

    <div class="mg-rail-footer">
        <a href="{{ route('reports.index') }}" class="mg-rail-item @if(Route::is('reports.*')) is-active @endif" title="Minha conta">
            <i class="ri-user-3-line"></i><span>Minha conta</span>
        </a>
        @can('view users')
        <a href="{{ route('team.index') }}" class="mg-rail-item @if(Route::is('team.*', 'account.collaborators', 'users.*')) is-active @endif" title="Meus colaboradores">
            <i class="ri-team-line"></i><span>Colaboradores</span>
        </a>
        @endcan
        <a href="{{ route('account.settings') }}" class="mg-rail-item @if(Route::is('account.settings', 'account.profile.*', 'settings.*')) is-active @endif" title="Configurações">
            <i class="ri-settings-3-line"></i><span>Configurações</span>
        </a>
    </div>
</aside>
