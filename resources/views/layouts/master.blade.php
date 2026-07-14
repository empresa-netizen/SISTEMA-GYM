<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.mg-theme-init')
    <meta charset="utf-8" />
    <title>@yield('title') | {{ config('brand.name', 'MGTEAM FITNESS & HEALTH') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ URL::asset('build/images/favicon.ico') }}">
    @role('super-admin')
        @include('layouts.head-css')
        <link href="{{ asset('css/mg-coaching.css') }}" rel="stylesheet">
    @else
        @include('layouts.mg-head-css')
    @endrole
    @unless(auth()->user()?->hasRole('super-admin'))
        <style id="mgteam-shell-inline">
            :root,
            html:not([data-mg-theme]),
            [data-mg-theme="dark"] {
                --mg-bg: #020817;
                --mg-bg-elevated: #09111f;
                --mg-surface: #111a2b;
                --mg-surface-2: #151f32;
                --mg-surface-hover: #1d2a42;
                --mg-card-border: rgba(87, 111, 148, 0.28);
                --mg-border: rgba(87, 111, 148, 0.22);
                --mg-border-strong: rgba(121, 146, 184, 0.38);
                --mg-blue: #3B95FF;
                --mg-blue-soft: rgba(59, 149, 255, 0.16);
                --mg-blue-deep: #1F5FA8;
                --mg-text: #F6F8FC;
                --mg-muted: #9EAAC0;
                --mg-muted-dim: #657187;
                --mg-danger: #F46F68;
                --mg-warning: #F6B23D;
                --mg-success: #22C986;
                --mg-rail-width: 4.25rem;
                --mg-rail-expanded: 17.45rem;
                --mg-header-height: 4.78rem;
                --mg-radius: 0.8rem;
                --mg-radius-lg: 1.08rem;
                --mg-page-gradient: linear-gradient(180deg, #020817 0%, #030817 100%);
            }

            body.mg-app {
                overflow-x: hidden;
                background: var(--mg-page-gradient) !important;
                color: var(--mg-text);
                font-size: 0.875rem;
            }

            body.mg-app a {
                color: #BBD7FF;
            }

            body.mg-app a:hover {
                color: #FFFFFF;
            }

            body.mg-app .mg-main {
                min-width: 0;
                margin-left: var(--mg-rail-width);
            }

            body.mg-rail-expanded .mg-main {
                margin-left: var(--mg-rail-expanded);
            }

            body.mg-app .mg-content {
                max-width: none;
                padding: 2.35rem 1.95rem 4rem;
                background: #020817;
            }

            .mg-rail {
                width: var(--mg-rail-width);
                overflow: visible;
                background: linear-gradient(180deg, #193059 0%, #142849 58%, #0B1629 100%) !important;
                border-right: 1px solid rgba(78, 104, 143, 0.35) !important;
                box-shadow: inset -1px 0 0 rgba(255, 255, 255, 0.03);
                z-index: 1030;
            }

            .mg-rail.is-expanded {
                width: var(--mg-rail-expanded);
            }

            .mg-rail-brand-wrap {
                min-height: 6.15rem;
                padding: 1.35rem 1.25rem 0.82rem;
                background: transparent !important;
            }

            .mg-rail-brand-wrap .mg-logo-img {
                height: 2.18rem !important;
                max-width: 7.6rem;
                object-fit: contain;
                filter: brightness(0) invert(1);
            }

            .mg-rail-toggle {
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

            .mg-rail-toggle:hover {
                background: rgba(128, 153, 190, 0.36) !important;
            }

            .mg-rail-nav {
                gap: 0.32rem;
                padding: 0.48rem 0.66rem 0.9rem;
            }

            .mg-rail-footer {
                padding: 0.75rem 0.66rem;
                border-top: 1px solid rgba(114, 141, 180, 0.18);
                background: rgba(4, 12, 25, 0.24);
            }

            .mg-rail.is-expanded .mg-rail-item,
            .mg-rail.is-expanded .mg-rail-trigger {
                min-height: 3.8rem;
                padding: 0 1rem;
                border-radius: 0.78rem;
                gap: 1rem;
            }

            .mg-rail-item,
            .mg-rail-trigger {
                color: rgba(240, 246, 255, 0.86) !important;
                font-size: 0.98rem;
                font-weight: 760;
                letter-spacing: -0.02em;
            }

            .mg-rail-item i {
                width: 1.55rem;
                color: currentColor !important;
                font-size: 1.42rem;
            }

            .mg-rail-item:hover,
            .mg-rail-item.is-active,
            .mg-rail-group.is-active > .mg-rail-trigger {
                background: rgba(118, 142, 181, 0.28) !important;
                color: #FFFFFF !important;
                box-shadow: none !important;
            }

            body.mg-rail-expanded .mg-rail-group.is-open > .mg-rail-flyout {
                margin: 0.12rem 0 0.62rem 1rem;
                padding: 0.12rem 0 0.18rem 0.82rem;
                border-left: 2px solid rgba(138, 163, 201, 0.36);
            }

            .mg-rail-flyout-link {
                min-height: 2.52rem;
                padding: 0 0.74rem;
                border-radius: 0.58rem;
                color: rgba(240, 246, 255, 0.84) !important;
                font-size: 0.93rem;
                font-weight: 720;
            }

            .mg-rail-flyout-link:hover,
            .mg-rail-flyout-link.is-active {
                background: rgba(118, 142, 181, 0.24) !important;
                color: #FFFFFF !important;
            }

            .mg-header {
                height: var(--mg-header-height);
                padding: 0 1.38rem 0 1.28rem;
                background: #101929 !important;
                border-bottom: 1px solid rgba(67, 88, 119, 0.38) !important;
                box-shadow: none !important;
            }

            .mg-header-left,
            .mg-header-right {
                gap: 0.72rem;
            }

            .mg-header-btn {
                width: 2.85rem;
                height: 2.85rem;
                border: 1px solid rgba(103, 128, 163, 0.42) !important;
                border-radius: 0.88rem;
                background: #162235 !important;
                color: #D3DEED !important;
                font-size: 1.25rem;
            }

            .mg-header-btn:hover {
                background: #1B2A41 !important;
                border-color: rgba(143, 168, 204, 0.58) !important;
            }

            .mg-header-pill,
            .mg-btn-primary,
            body.mg-app .btn-primary {
                min-height: 2.65rem;
                border: 1px solid #3B95FF !important;
                border-radius: 0.82rem;
                background: #3B95FF !important;
                color: #FFFFFF !important;
                font-size: 0.94rem;
                font-weight: 850;
                box-shadow: none !important;
            }

            .mg-header-pill:hover,
            .mg-btn-primary:hover,
            body.mg-app .btn-primary:hover {
                background: #54A4FF !important;
                border-color: #54A4FF !important;
                color: #FFFFFF !important;
            }

            .mg-header-pill--ghost,
            .mg-btn-ghost,
            body.mg-app .btn-light {
                min-height: 2.65rem;
                border: 1px solid rgba(103, 128, 163, 0.42) !important;
                border-radius: 0.82rem;
                background: #172235 !important;
                color: #D8E1EF !important;
                font-size: 0.94rem;
                font-weight: 800;
                box-shadow: none !important;
            }

            .mg-header-pill--ghost:hover,
            .mg-btn-ghost:hover,
            body.mg-app .btn-light:hover {
                background: #202D43 !important;
                border-color: rgba(129, 153, 190, 0.54) !important;
            }

            .mg-goal-bar {
                min-width: 20.7rem;
                color: #C7D7EE !important;
                font-size: 0.88rem;
                font-weight: 900;
            }

            .mg-goal-track {
                height: 1.7rem;
                border-radius: 999px;
                background: #40516A !important;
            }

            .mg-goal-fill,
            body.mg-app .progress-bar {
                background: #3B95FF !important;
            }

            .mg-avatar {
                width: 3.05rem;
                height: 3.05rem;
                border-radius: 999px;
                background: #2D7DDA !important;
                color: #FFFFFF !important;
                font-size: 0.95rem;
                font-weight: 900;
            }

            .mg-notify-badge {
                background: #FF3B30 !important;
            }

            .mg-panel,
            .mg-metric-card,
            .mg-client-card,
            body.mg-app .card,
            .mg-clients-filters,
            .mg-client-tab-body,
            .mg-rx-card.mg-prescription-card,
            .mg-rx-builder,
            .mg-rx-meal-builder,
            .mg-rx-workout-grid,
            .mg-chat-layout {
                border: 1px solid var(--mg-card-border) !important;
                background: var(--mg-surface) !important;
                color: var(--mg-text);
                box-shadow: none !important;
            }

            .mg-panel:hover,
            .mg-metric-card:hover,
            .mg-client-card:hover,
            .mg-rx-card.mg-prescription-card:hover {
                border-color: rgba(108, 135, 174, 0.42) !important;
                background: #131E31 !important;
            }

            .mg-section-pill,
            .mg-chip,
            .mg-status-badge,
            .mg-rx-mini-chip,
            .mg-prescription-card__meta span {
                border-color: rgba(112, 138, 174, 0.24) !important;
                background: rgba(112, 138, 174, 0.2) !important;
                color: #C8D6EA !important;
                font-weight: 850;
            }

            .mg-chip--success,
            .mg-status-badge.is-ok {
                background: rgba(34, 201, 134, 0.18) !important;
                color: #86EFAC !important;
            }

            .mg-chip--warn,
            .mg-status-badge.is-warn {
                background: rgba(246, 178, 61, 0.18) !important;
                color: #FCD47B !important;
            }

            .mg-chip--danger,
            .mg-status-badge.is-missing,
            body.mg-app .badge.bg-danger {
                background: rgba(244, 111, 104, 0.18) !important;
                color: #FF9A95 !important;
            }

            body.mg-app .form-control,
            body.mg-app .form-select,
            .mg-field,
            .mg-rx-input.mg-field,
            .mg-rx-quiet-field.mg-field {
                min-height: 2.7rem;
                border: 1px solid rgba(73, 96, 128, 0.58) !important;
                border-radius: 0.72rem;
                background: #081422 !important;
                color: #F4F7FC !important;
                font-size: 0.9rem;
            }

            body.mg-app .form-control:focus,
            body.mg-app .form-select:focus,
            .mg-field:focus,
            .mg-rx-input.mg-field:focus,
            .mg-rx-quiet-field.mg-field:focus {
                border-color: rgba(59, 149, 255, 0.72) !important;
                box-shadow: 0 0 0 3px rgba(59, 149, 255, 0.18) !important;
            }

            .mg-field-label,
            body.mg-app .form-label {
                color: #D7E0EF !important;
                font-size: 0.82rem;
                font-weight: 800;
            }

            .mg-section-label,
            .mg-panel-label,
            .mg-prescription-card__eyebrow {
                color: #8D9AB0 !important;
                font-size: 0.72rem;
                font-weight: 900;
                letter-spacing: 0.13em;
            }

            .mg-page-title {
                color: #FFFFFF;
                font-size: 1.95rem;
                font-weight: 900;
                letter-spacing: -0.045em;
            }

            .mg-page-sub,
            body.mg-app .text-muted {
                color: #99A6BA !important;
            }

            .mg-icon-btn {
                border-color: rgba(80, 105, 140, 0.42) !important;
                background: #0B1728 !important;
                color: #8EA3C0 !important;
            }

            .mg-icon-btn:hover {
                border-color: rgba(59, 149, 255, 0.44) !important;
                background: #102442 !important;
                color: #DDEBFF !important;
            }

            .mg-client-profile {
                gap: 1.25rem;
            }

            .mg-client-hero {
                padding: 1.85rem 1.65rem 1.55rem;
                border: 1px solid rgba(48, 104, 177, 0.58) !important;
                border-radius: 1rem;
                background: linear-gradient(105deg, #185493 0%, #113F73 52%, #0C315B 100%) !important;
                box-shadow: none !important;
            }

            .mg-client-hero__avatar,
            .mg-client-hero__avatar-img {
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

            .mg-client-hero__name {
                color: #FFFFFF;
                font-size: 2rem;
                font-weight: 950;
                letter-spacing: -0.055em;
            }

            .mg-client-hero__contact,
            .mg-client-chips--hero .mg-chip {
                color: #D7E7FF !important;
            }

            .mg-client-tabs {
                gap: 2.25rem;
                padding: 0 1.65rem;
                border-bottom: 1px solid rgba(87, 111, 148, 0.28) !important;
            }

            .mg-client-tabs__link {
                padding: 1.25rem 0 1rem;
                color: #AEBBD0 !important;
                font-size: 0.95rem;
                font-weight: 860;
            }

            .mg-client-tabs__link.is-active {
                color: #FFFFFF !important;
                border-bottom: 3px solid #FFFFFF !important;
            }

            .mg-chat-fab {
                background: #3B95FF !important;
                color: #FFFFFF !important;
                box-shadow: 0 12px 32px rgba(59, 149, 255, 0.35) !important;
            }

            @media (max-width: 1199.98px) {
                body.mg-app .mg-main,
                body.mg-rail-expanded .mg-main {
                    margin-left: 0;
                }

                body.mg-app .mg-content {
                    padding: 1.15rem 0.85rem 5.25rem;
                }
            }
        </style>
    @endunless
    @stack('styles')
</head>
<body
    @unless(auth()->user()?->hasRole('super-admin')) class="mg-app" @endunless
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
    <div class="mg-layout">
        @include('layouts.mg-sidebar')
        <div class="mg-main">
            @include('layouts.mg-topbar')
            <main class="mg-content">
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
            @include('layouts.mg-bottom-nav')
        </div>
    </div>
    @include('layouts.mg-search-modal')
    @include('layouts.mg-chat-widget')
    @include('layouts.mg-vendor-scripts')
@endif
@include('partials.sentry-browser')
</body>
</html>
