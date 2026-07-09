@role('super-admin')
    @include('layouts.super-admin-sidebar')
@else
<div class="app-menu navbar-menu">
    <div class="navbar-brand-box">
        <a href="{{ route('dashboard') }}" class="prime-sidebar-brand">
            <span class="prime-logo-mark">P</span>
            <span>
                <strong>prime</strong>
                <span>COACHING</span>
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span>Menu</span></li>

                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link menu-link @if(Route::is('dashboard')) active @endif">
                        <i class="ri-dashboard-2-line"></i>
                        <span>Resumo</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('events.index') }}" class="nav-link menu-link @if(Route::is('events.*')) active @endif">
                        <i class="ri-calendar-line"></i>
                        <span>Agenda</span>
                    </a>
                </li>

                @canany(['view members', 'view plans'])
                <li class="nav-item">
                    <a class="nav-link menu-link @if(Route::is('members.*') || Route::is('membership-plans.*')) active @endif" href="#sidebarClients" data-bs-toggle="collapse" role="button" aria-expanded="{{ Route::is('members.*') || Route::is('membership-plans.*') ? 'true' : 'false' }}">
                        <i class="ri-group-line"></i>
                        <span>Clientes</span>
                    </a>
                    <div class="collapse menu-dropdown @if(Route::is('members.*') || Route::is('membership-plans.*')) show @endif" id="sidebarClients">
                        <ul class="nav nav-sm flex-column">
                            @can('view members')
                            <li class="nav-item"><a href="{{ route('members.index') }}" class="nav-link @if(Route::is('members.*')) active @endif">Todos os clientes</a></li>
                            @endcan
                            @can('view plans')
                            <li class="nav-item"><a href="{{ route('membership-plans.index') }}" class="nav-link @if(Route::is('membership-plans.*')) active @endif">Planos</a></li>
                            @endcan
                        </ul>
                    </div>
                </li>
                @endcanany

                @can('view members')
                <li class="nav-item">
                    <a class="nav-link menu-link @if(Route::is('workouts.*') || Route::is('healths.*')) active @endif" href="#sidebarWorkouts" data-bs-toggle="collapse" role="button">
                        <i class="ri-run-line"></i>
                        <span>Treinos</span>
                    </a>
                    <div class="collapse menu-dropdown @if(Route::is('workouts.*') || Route::is('healths.*')) show @endif" id="sidebarWorkouts">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item"><a href="{{ route('workouts.index') }}" class="nav-link">Prescrições</a></li>
                            <li class="nav-item"><a href="{{ route('workouts.create') }}" class="nav-link">Nova prescrição</a></li>
                            <li class="nav-item"><a href="{{ route('healths.index') }}" class="nav-link">Evolução</a></li>
                        </ul>
                    </div>
                </li>
                @endcan

                <li class="nav-item">
                    <a class="nav-link menu-link @if(Route::is('library.*') || Route::is('exercises.*')) active @endif" href="#sidebarLibrary" data-bs-toggle="collapse" role="button" aria-expanded="{{ Route::is('library.*') || Route::is('exercises.*') ? 'true' : 'false' }}">
                        <i class="ri-play-list-2-line"></i>
                        <span>Biblioteca</span>
                    </a>
                    <div class="collapse menu-dropdown @if(Route::is('library.*') || Route::is('exercises.*')) show @endif" id="sidebarLibrary">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item"><a href="{{ route('exercises.index') }}" class="nav-link @if(Route::is('exercises.*')) active @endif">Exercícios</a></li>
                            <li class="nav-item"><a href="{{ route('library.diet.index') }}" class="nav-link @if(Route::is('library.diet.*')) active @endif">Dieta</a></li>
                        </ul>
                    </div>
                </li>

                @can('view payments')
                <li class="nav-item">
                    <a class="nav-link menu-link @if(Route::is('invoices.*') || Route::is('expenses.*')) active @endif" href="#sidebarFinance" data-bs-toggle="collapse" role="button">
                        <i class="ri-money-dollar-circle-line"></i>
                        <span>Financeiro</span>
                    </a>
                    <div class="collapse menu-dropdown @if(Route::is('invoices.*') || Route::is('expenses.*')) show @endif" id="sidebarFinance">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item"><a href="{{ route('invoices.index') }}" class="nav-link">Vendas</a></li>
                            <li class="nav-item"><a href="{{ route('expenses.index') }}" class="nav-link">Despesas</a></li>
                        </ul>
                    </div>
                </li>
                @endcan

                @can('manage settings')
                <li class="nav-item">
                    <a href="{{ route('settings.index') }}" class="nav-link menu-link @if(Route::is('settings.*')) active @endif">
                        <i class="ri-settings-3-line"></i>
                        <span>Configurações</span>
                    </a>
                </li>
                @endcan

                <li class="nav-item">
                    <a href="{{ route('help') }}" class="nav-link menu-link @if(Route::is('help', 'support-tickets.*')) active @endif">
                        <i class="ri-customer-service-2-line"></i>
                        <span>Suporte</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="sidebar-background"></div>
</div>
<div class="vertical-overlay"></div>
@endrole
