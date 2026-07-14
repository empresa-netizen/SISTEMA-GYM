@yield('css')
@stack('css')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="{{ URL::asset('build/css/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ URL::asset('build/css/icons.min.css') }}" rel="stylesheet">
<link href="{{ asset('css/datatables.bootstrap5.css') }}" rel="stylesheet">
<link href="{{ asset('css/responsive.bootstrap5.css') }}" rel="stylesheet">
<link href="{{ asset('css/buttons.bootstrap5.css') }}" rel="stylesheet">
<link href="{{ asset('css/sweetalert2.min.css') }}" rel="stylesheet">
<link href="{{ asset('css/mg-coaching.css') }}?v={{ filemtime(public_path('css/mg-coaching.css')) }}" rel="stylesheet">
<link href="{{ asset('css/mg-app.css') }}?v={{ filemtime(public_path('css/mg-app.css')) }}" rel="stylesheet">
