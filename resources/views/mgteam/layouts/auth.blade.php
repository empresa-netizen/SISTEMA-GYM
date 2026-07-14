<!doctype html>
<html lang="pt-BR">
<head>
    @include('layouts.mg-theme-init')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | {{ config('brand.name', 'MGTEAM FITNESS & HEALTH') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/mg-coaching.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body class="mg-auth" data-mg-theme="dark">
    <div class="mg-topbar">
        <button type="button" class="mg-icon-btn" data-mg-theme-toggle aria-label="Alternar tema">
            <i class="ri-sun-line"></i>
        </button>
        <div class="mg-lang">🇧🇷 Português (Brasil)</div>
    </div>

    @yield('content')

    @yield('script')
    <script src="{{ asset('js/mg-theme.js') }}"></script>
</body>
</html>
