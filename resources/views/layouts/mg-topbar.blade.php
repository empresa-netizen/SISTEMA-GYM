@php
    $initials = collect(explode(' ', auth()->user()->name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('');
    $monthRevenue = \App\Models\InvoicePayment::whereHas('invoice', fn ($q) => $q->where('parent_id', parentId()))
        ->where('payment_date', '>=', now()->startOfMonth())
        ->sum('amount');
    $goal = (float) (settings('monthly_goal', 10000) ?: 10000);
    $progress = $goal > 0 ? min(100, ($monthRevenue / $goal) * 100) : 0;
    $unreadNotifications = auth()->user()->unreadNotifications()->latest()->take(8)->get();
    $unreadCount = auth()->user()->unreadNotifications()->count();
@endphp
<header class="mg-header">
    <div class="mg-header-left">
        <button type="button" class="mg-header-btn d-xl-none" id="mgMobileMenuBtn" title="Menu" aria-label="Abrir menu">
            <i class="ri-menu-line"></i>
        </button>
        <button type="button" class="mg-header-btn" title="Buscar" data-bs-toggle="modal" data-bs-target="#mgSearchModal"><i class="ri-search-line"></i></button>
        <a href="{{ route('help') }}" class="mg-header-pill d-none d-sm-inline-flex"><i class="ri-question-answer-line"></i> Tirar dúvidas</a>
        <a href="{{ route('patch-notes') }}" class="mg-header-pill mg-header-pill--ghost d-none d-md-inline-flex"><i class="ri-megaphone-line"></i> Notas de Atualização</a>
    </div>

    <div class="mg-header-center d-none d-lg-flex">
        <div class="mg-goal-bar">
            <span>R$ {{ number_format($monthRevenue, 0, ',', '.') }}</span>
            <div class="mg-goal-track"><div class="mg-goal-fill" style="width: {{ $progress }}%"></div></div>
            <span>R$ {{ number_format($goal / 1000, 0, ',', '.') }}K</span>
        </div>
    </div>

    <div class="mg-header-right">
        <div class="dropdown">
            <button type="button" class="mg-header-btn mg-header-btn--notify" title="Notificações" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="ri-notification-3-line"></i>
                @if($unreadCount > 0)
                    <span class="mg-notify-badge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                @endif
            </button>
            <div class="dropdown-menu dropdown-menu-end mg-dropdown mg-notify-dropdown">
                <div class="mg-notify-dropdown__head">
                    <strong>Notificações</strong>
                    @if($unreadCount > 0)
                        <form method="POST" action="{{ route('notifications.read-all') }}">
                            @csrf
                            <button type="submit" class="btn btn-link btn-sm p-0">Marcar todas</button>
                        </form>
                    @endif
                </div>
                @forelse($unreadNotifications as $notification)
                    @php $data = $notification->data; @endphp
                    <a class="dropdown-item mg-notify-item" href="{{ route('notifications.read', $notification->id) }}"
                       onclick="event.preventDefault(); document.getElementById('notify-read-{{ $notification->id }}').submit();">
                        <span class="mg-notify-item__icon"><i class="{{ $data['icon'] ?? 'ri-notification-3-line' }}"></i></span>
                        <span>
                            <strong>{{ $data['title'] ?? 'Alerta' }}</strong>
                            <small>{{ \Illuminate\Support\Str::limit($data['body'] ?? '', 70) }}</small>
                        </span>
                    </a>
                    <form id="notify-read-{{ $notification->id }}" method="POST" action="{{ route('notifications.read', $notification->id) }}" class="d-none">@csrf</form>
                @empty
                    <div class="mg-notify-empty">Nenhuma notificação nova.</div>
                @endforelse
                <div class="mg-notify-dropdown__foot">
                    <a href="{{ route('notifications.inbox') }}">Ver todas</a>
                </div>
            </div>
        </div>
        <div class="dropdown">
            <button class="mg-avatar" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                {{ strtoupper($initials) }}
            </button>
            <ul class="dropdown-menu dropdown-menu-end mg-dropdown">
                <li><span class="dropdown-item-text fw-semibold">{{ auth()->user()->name }}</span></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="{{ route('reports.index') }}">Perfil</a></li>
                <li><a class="dropdown-item" href="{{ route('finance.index') }}">Financeiro</a></li>
                <li><a class="dropdown-item" href="{{ route('account.settings') }}">Configurações</a></li>
                <li><a class="dropdown-item" href="{{ route('notifications.inbox') }}">Notificações</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><span class="dropdown-item-text small text-muted">Tema</span></li>
                <li><button type="button" class="dropdown-item" data-mg-theme-set="light">Tema claro</button></li>
                <li><button type="button" class="dropdown-item" data-mg-theme-set="dark">Tema escuro</button></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">Sair</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
