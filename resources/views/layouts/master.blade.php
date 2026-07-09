<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.prime-theme-init')
    <meta charset="utf-8" />
    <title>@yield('title') | {{ config('brand.name', 'MGTEAM FITNESS & HEALTH') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ URL::asset('build/images/favicon.ico') }}">
    @role('super-admin')
        @include('layouts.head-css')
        <link href="{{ asset('css/prime-coaching.css') }}" rel="stylesheet">
    @else
        @include('layouts.prime-head-css')
    @endrole
    @stack('styles')
</head>
<body @unless(auth()->user()?->hasRole('super-admin')) class="prime-app" @endunless>
@role('super-admin')
    @include('layouts.body')
    <div id="layout-wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">@yield('content')</div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
    @include('layouts.vendor-scripts')
@else
    <div class="prime-layout">
        @include('layouts.prime-sidebar')
        <div class="prime-main">
            @include('layouts.prime-topbar')
            <main class="prime-content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @yield('content')
            </main>
            @include('layouts.prime-bottom-nav')
        </div>
    </div>
    @include('layouts.prime-search-modal')
    @include('layouts.prime-chat-widget')
    @include('layouts.prime-vendor-scripts')
@endif
@include('partials.sentry-browser')
</body>
</html>
