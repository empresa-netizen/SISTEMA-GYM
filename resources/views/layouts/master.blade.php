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
    @unless(auth()->user()?->hasRole('super-admin'))
        <style id="primecoaching-shell-inline">
            :root,
            html:not([data-prime-theme]),
            [data-prime-theme="dark"] {
                --prime-bg: #020817;
                --prime-bg-elevated: #09111f;
                --prime-surface: #111a2b;
                --prime-surface-2: #151f32;
                --prime-surface-hover: #1d2a42;
                --prime-card-border: rgba(87, 111, 148, 0.28);
                --prime-border: rgba(87, 111, 148, 0.22);
                --prime-border-strong: rgba(121, 146, 184, 0.38);
                --prime-blue: #3B95FF;
                --prime-blue-soft: rgba(59, 149, 255, 0.16);
                --prime-blue-deep: #1F5FA8;
                --prime-text: #F6F8FC;
                --prime-muted: #9EAAC0;
                --prime-muted-dim: #657187;
                --prime-danger: #F46F68;
                --prime-warning: #F6B23D;
                --prime-success: #22C986;
                --prime-rail-width: 4.25rem;
                --prime-rail-expanded: 17.45rem;
                --prime-header-height: 4.78rem;
                --prime-radius: 0.8rem;
                --prime-radius-lg: 1.08rem;
                --prime-page-gradient: linear-gradient(180deg, #020817 0%, #030817 100%);
            }

            body.prime-app {
                overflow-x: hidden;
                background: var(--prime-page-gradient) !important;
                color: var(--prime-text);
                font-size: 0.875rem;
            }

            body.prime-app a {
                color: #BBD7FF;
            }

            body.prime-app a:hover {
                color: #FFFFFF;
            }

            body.prime-app .prime-main {
                min-width: 0;
                margin-left: var(--prime-rail-width);
            }

            body.prime-rail-expanded .prime-main {
                margin-left: var(--prime-rail-expanded);
            }

            body.prime-app .prime-content {
                max-width: none;
                padding: 2.35rem 1.95rem 4rem;
                background: #020817;
            }

            .prime-rail {
                width: var(--prime-rail-width);
                overflow: visible;
                background: linear-gradient(180deg, #193059 0%, #142849 58%, #0B1629 100%) !important;
                border-right: 1px solid rgba(78, 104, 143, 0.35) !important;
                box-shadow: inset -1px 0 0 rgba(255, 255, 255, 0.03);
                z-index: 1030;
            }

            .prime-rail.is-expanded {
                width: var(--prime-rail-expanded);
            }

            .prime-rail-brand-wrap {
                min-height: 6.15rem;
                padding: 1.35rem 1.25rem 0.82rem;
                background: transparent !important;
            }

            .prime-rail-brand-wrap .prime-logo-img {
                height: 2.18rem !important;
                max-width: 7.6rem;
                object-fit: contain;
                filter: brightness(0) invert(1);
            }

            .prime-rail-toggle {
                position: absolute;
                top: 1.95rem;
                right: 1.05rem;
                width: 2.7rem;
                height: 2.7rem;
                margin: 0;
                border-radius: 0.82rem;
                background: rgba(106, 130, 166, 0.28) !important;
                color: #DDE7F7 !important;
            }

            .prime-rail-toggle:hover {
                background: rgba(128, 153, 190, 0.36) !important;
            }

            .prime-rail-nav {
                gap: 0.32rem;
                padding: 0.48rem 0.66rem 0.9rem;
            }

            .prime-rail-footer {
                padding: 0.75rem 0.66rem;
                border-top: 1px solid rgba(114, 141, 180, 0.18);
                background: rgba(4, 12, 25, 0.24);
            }

            .prime-rail.is-expanded .prime-rail-item,
            .prime-rail.is-expanded .prime-rail-trigger {
                min-height: 3.8rem;
                padding: 0 1rem;
                border-radius: 0.78rem;
                gap: 1rem;
            }

            .prime-rail-item,
            .prime-rail-trigger {
                color: rgba(240, 246, 255, 0.86) !important;
                font-size: 0.98rem;
                font-weight: 760;
                letter-spacing: -0.02em;
            }

            .prime-rail-item i {
                width: 1.55rem;
                color: currentColor !important;
                font-size: 1.42rem;
            }

            .prime-rail-item:hover,
            .prime-rail-item.is-active,
            .prime-rail-group.is-active > .prime-rail-trigger {
                background: rgba(118, 142, 181, 0.28) !important;
                color: #FFFFFF !important;
                box-shadow: none !important;
            }

            body.prime-rail-expanded .prime-rail-group.is-open > .prime-rail-flyout {
                margin: 0.12rem 0 0.62rem 1rem;
                padding: 0.12rem 0 0.18rem 0.82rem;
                border-left: 2px solid rgba(138, 163, 201, 0.36);
            }

            .prime-rail-flyout-link {
                min-height: 2.52rem;
                padding: 0 0.74rem;
                border-radius: 0.58rem;
                color: rgba(240, 246, 255, 0.84) !important;
                font-size: 0.93rem;
                font-weight: 720;
            }

            .prime-rail-flyout-link:hover,
            .prime-rail-flyout-link.is-active {
                background: rgba(118, 142, 181, 0.24) !important;
                color: #FFFFFF !important;
            }

            .prime-header {
                height: var(--prime-header-height);
                padding: 0 1.38rem 0 1.28rem;
                background: #101929 !important;
                border-bottom: 1px solid rgba(67, 88, 119, 0.38) !important;
                box-shadow: none !important;
            }

            .prime-header-left,
            .prime-header-right {
                gap: 0.72rem;
            }

            .prime-header-btn {
                width: 2.85rem;
                height: 2.85rem;
                border: 1px solid rgba(103, 128, 163, 0.42) !important;
                border-radius: 0.88rem;
                background: #162235 !important;
                color: #D3DEED !important;
                font-size: 1.25rem;
            }

            .prime-header-btn:hover {
                background: #1B2A41 !important;
                border-color: rgba(143, 168, 204, 0.58) !important;
            }

            .prime-header-pill,
            .prime-btn-primary,
            body.prime-app .btn-primary {
                min-height: 2.65rem;
                border: 1px solid #3B95FF !important;
                border-radius: 0.82rem;
                background: #3B95FF !important;
                color: #FFFFFF !important;
                font-size: 0.94rem;
                font-weight: 850;
                box-shadow: none !important;
            }

            .prime-header-pill:hover,
            .prime-btn-primary:hover,
            body.prime-app .btn-primary:hover {
                background: #54A4FF !important;
                border-color: #54A4FF !important;
                color: #FFFFFF !important;
            }

            .prime-header-pill--ghost,
            .prime-btn-ghost,
            body.prime-app .btn-light {
                min-height: 2.65rem;
                border: 1px solid rgba(103, 128, 163, 0.42) !important;
                border-radius: 0.82rem;
                background: #172235 !important;
                color: #D8E1EF !important;
                font-size: 0.94rem;
                font-weight: 800;
                box-shadow: none !important;
            }

            .prime-header-pill--ghost:hover,
            .prime-btn-ghost:hover,
            body.prime-app .btn-light:hover {
                background: #202D43 !important;
                border-color: rgba(129, 153, 190, 0.54) !important;
            }

            .prime-goal-bar {
                min-width: 20.7rem;
                color: #C7D7EE !important;
                font-size: 0.88rem;
                font-weight: 900;
            }

            .prime-goal-track {
                height: 1.7rem;
                border-radius: 999px;
                background: #40516A !important;
            }

            .prime-goal-fill,
            body.prime-app .progress-bar {
                background: #3B95FF !important;
            }

            .prime-avatar {
                width: 3.05rem;
                height: 3.05rem;
                border-radius: 999px;
                background: #2D7DDA !important;
                color: #FFFFFF !important;
                font-size: 0.95rem;
                font-weight: 900;
            }

            .prime-notify-badge {
                background: #FF3B30 !important;
            }

            .prime-panel,
            .prime-metric-card,
            .prime-client-card,
            body.prime-app .card,
            .prime-clients-filters,
            .prime-client-tab-body,
            .prime-rx-card.prime-prescription-card,
            .prime-rx-builder,
            .prime-rx-meal-builder,
            .prime-rx-workout-grid,
            .prime-chat-layout {
                border: 1px solid var(--prime-card-border) !important;
                background: var(--prime-surface) !important;
                color: var(--prime-text);
                box-shadow: none !important;
            }

            .prime-panel:hover,
            .prime-metric-card:hover,
            .prime-client-card:hover,
            .prime-rx-card.prime-prescription-card:hover {
                border-color: rgba(108, 135, 174, 0.42) !important;
                background: #131E31 !important;
            }

            .prime-section-pill,
            .prime-chip,
            .prime-status-badge,
            .prime-rx-mini-chip,
            .prime-prescription-card__meta span {
                border-color: rgba(112, 138, 174, 0.24) !important;
                background: rgba(112, 138, 174, 0.2) !important;
                color: #C8D6EA !important;
                font-weight: 850;
            }

            .prime-chip--success,
            .prime-status-badge.is-ok {
                background: rgba(34, 201, 134, 0.18) !important;
                color: #86EFAC !important;
            }

            .prime-chip--warn,
            .prime-status-badge.is-warn {
                background: rgba(246, 178, 61, 0.18) !important;
                color: #FCD47B !important;
            }

            .prime-chip--danger,
            .prime-status-badge.is-missing,
            body.prime-app .badge.bg-danger {
                background: rgba(244, 111, 104, 0.18) !important;
                color: #FF9A95 !important;
            }

            body.prime-app .form-control,
            body.prime-app .form-select,
            .prime-field,
            .prime-rx-input.prime-field,
            .prime-rx-quiet-field.prime-field {
                min-height: 2.7rem;
                border: 1px solid rgba(73, 96, 128, 0.58) !important;
                border-radius: 0.72rem;
                background: #081422 !important;
                color: #F4F7FC !important;
                font-size: 0.9rem;
            }

            body.prime-app .form-control:focus,
            body.prime-app .form-select:focus,
            .prime-field:focus,
            .prime-rx-input.prime-field:focus,
            .prime-rx-quiet-field.prime-field:focus {
                border-color: rgba(59, 149, 255, 0.72) !important;
                box-shadow: 0 0 0 3px rgba(59, 149, 255, 0.18) !important;
            }

            .prime-field-label,
            body.prime-app .form-label {
                color: #D7E0EF !important;
                font-size: 0.82rem;
                font-weight: 800;
            }

            .prime-section-label,
            .prime-panel-label,
            .prime-prescription-card__eyebrow {
                color: #8D9AB0 !important;
                font-size: 0.72rem;
                font-weight: 900;
                letter-spacing: 0.13em;
            }

            .prime-page-title {
                color: #FFFFFF;
                font-size: 1.95rem;
                font-weight: 900;
                letter-spacing: -0.045em;
            }

            .prime-page-sub,
            body.prime-app .text-muted {
                color: #99A6BA !important;
            }

            .prime-icon-btn {
                border-color: rgba(80, 105, 140, 0.42) !important;
                background: #0B1728 !important;
                color: #8EA3C0 !important;
            }

            .prime-icon-btn:hover {
                border-color: rgba(59, 149, 255, 0.44) !important;
                background: #102442 !important;
                color: #DDEBFF !important;
            }

            .prime-client-profile {
                gap: 1.25rem;
            }

            .prime-client-hero {
                padding: 1.85rem 1.65rem 1.55rem;
                border: 1px solid rgba(48, 104, 177, 0.58) !important;
                border-radius: 1rem;
                background: linear-gradient(105deg, #185493 0%, #113F73 52%, #0C315B 100%) !important;
                box-shadow: none !important;
            }

            .prime-client-hero__avatar,
            .prime-client-hero__avatar-img {
                width: 5.4rem;
                height: 5.4rem;
                border: 3px solid rgba(112, 176, 255, 0.6) !important;
                border-radius: 999px;
                background: #2D7DDA !important;
                color: #FFFFFF !important;
                font-size: 1.05rem;
                font-weight: 950;
                box-shadow: none !important;
            }

            .prime-client-hero__name {
                color: #FFFFFF;
                font-size: 2rem;
                font-weight: 950;
                letter-spacing: -0.055em;
            }

            .prime-client-hero__contact,
            .prime-client-chips--hero .prime-chip {
                color: #D7E7FF !important;
            }

            .prime-client-tabs {
                gap: 2.25rem;
                padding: 0 1.65rem;
                border-bottom: 1px solid rgba(87, 111, 148, 0.28) !important;
            }

            .prime-client-tabs__link {
                padding: 1.25rem 0 1rem;
                color: #AEBBD0 !important;
                font-size: 0.95rem;
                font-weight: 860;
            }

            .prime-client-tabs__link.is-active {
                color: #FFFFFF !important;
                border-bottom: 3px solid #FFFFFF !important;
            }

            .prime-chat-fab {
                background: #3B95FF !important;
                color: #FFFFFF !important;
                box-shadow: 0 12px 32px rgba(59, 149, 255, 0.35) !important;
            }

            @media (max-width: 1199.98px) {
                body.prime-app .prime-main,
                body.prime-rail-expanded .prime-main {
                    margin-left: 0;
                }

                body.prime-app .prime-content {
                    padding: 1.15rem 0.85rem 5.25rem;
                }
            }
        </style>
    @endunless
    @stack('styles')
</head>
<body
    @unless(auth()->user()?->hasRole('super-admin')) class="prime-app" @endunless
    @auth data-user-id="{{ auth()->id() }}" @endauth
>
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
