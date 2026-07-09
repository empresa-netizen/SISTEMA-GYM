<!doctype html>
<html lang="pt-BR">
<head>
    @include('layouts.prime-theme-init')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | {{ config('brand.name', 'MGTEAM FITNESS & HEALTH') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/prime-coaching.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body class="prime-auth">
    <div class="prime-topbar">
        <button type="button" class="prime-icon-btn" data-prime-theme-toggle aria-label="Alternar tema">
            <i class="ri-sun-line"></i>
        </button>
        <div class="prime-lang">🇧🇷 Português (Brasil)</div>
    </div>

    @yield('content')

    @yield('script')
    <script src="{{ asset('js/prime-theme.js') }}"></script>
</body>
</html>
