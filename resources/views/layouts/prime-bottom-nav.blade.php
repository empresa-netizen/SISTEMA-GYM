<nav class="prime-bottom-nav d-xl-none" aria-label="Navegação mobile">
    <button type="button" class="prime-bottom-nav-item" id="primeMobileMenuNavBtn" aria-label="Abrir menu">
        <i class="ri-menu-line"></i><span>Menu</span>
    </button>
    <button type="button" class="prime-bottom-nav-item" data-bs-toggle="modal" data-bs-target="#primeSearchModal">
        <i class="ri-search-line"></i><span>Buscar</span>
    </button>
    <a href="{{ route('dashboard') }}" class="prime-bottom-nav-item prime-bottom-nav-item--center @if(Route::is('dashboard')) is-active @endif">
        <i class="ri-home-4-line"></i><span>Resumo</span>
    </a>
    <a href="{{ route('feed.index') }}" class="prime-bottom-nav-item @if(Route::is('feed.*')) is-active @endif">
        <i class="ri-rss-line"></i><span>Feed</span>
    </a>
    <a href="{{ route('community.index') }}" class="prime-bottom-nav-item @if(Route::is('community.*')) is-active @endif">
        <i class="ri-community-line"></i><span>Comunidade</span>
    </a>
</nav>

<div class="prime-mobile-drawer" id="primeMobileDrawer" aria-hidden="true">
    <div class="prime-mobile-drawer__backdrop" data-prime-drawer-close></div>
    <aside class="prime-mobile-drawer__panel">
        <div class="prime-mobile-drawer__head">
            <strong>{{ config('brand.short', 'MGTEAM') }}</strong>
            <button type="button" class="prime-header-btn" data-prime-drawer-close aria-label="Fechar"><i class="ri-close-line"></i></button>
        </div>
        <nav class="prime-mobile-drawer__nav">
            <a href="{{ route('dashboard') }}"><i class="ri-home-4-line"></i> Resumo</a>
            <a href="{{ route('members.index') }}"><i class="ri-group-line"></i> Clientes</a>
            <a href="{{ route('library.hub') }}"><i class="ri-book-open-line"></i> Bibliotecas</a>
            <a href="{{ route('finance.index') }}"><i class="ri-wallet-3-line"></i> Financeiro</a>
            <a href="{{ route('products.hub') }}"><i class="ri-shopping-bag-3-line"></i> Produtos</a>
            <a href="{{ route('apps.index') }}"><i class="ri-smartphone-line"></i> Apps</a>
            <a href="{{ route('feed.index') }}"><i class="ri-rss-line"></i> Feed</a>
            <a href="{{ route('community.index') }}"><i class="ri-community-line"></i> Comunidade</a>
            <a href="{{ route('team.index') }}"><i class="ri-team-line"></i> Colaboradores</a>
            <a href="{{ route('account.settings') }}"><i class="ri-settings-3-line"></i> Configurações</a>
            <a href="{{ route('notifications.inbox') }}"><i class="ri-notification-3-line"></i> Notificações</a>
        </nav>
    </aside>
</div>
