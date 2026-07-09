{{-- Browser error tracking — ativo apenas quando SENTRY_LARAVEL_DSN estiver preenchido --}}
@if(filled(config('sentry.dsn')))
<script src="https://browser.sentry-cdn.com/8.47.0/bundle.min.js" crossorigin="anonymous"></script>
<script>
    if (window.Sentry) {
        Sentry.init({
            dsn: @json(config('sentry.dsn')),
            environment: @json(config('sentry.environment') ?: config('app.env')),
            release: @json(config('sentry.release')),
            tracesSampleRate: {{ (float) (config('sentry.traces_sample_rate') ?? 0.0) }},
        });
        @auth
        Sentry.setUser({
            id: @json((string) auth()->id()),
            email: @json(auth()->user()->email),
            username: @json(auth()->user()->name),
        });
        @endauth
    }
</script>
@endif
