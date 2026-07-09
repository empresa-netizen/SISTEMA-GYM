<a href="{{ auth()->check() ? route('dashboard') : route('welcome') }}" class="prime-logo">
    <span class="prime-logo-mark">{{ config('brand.logo_mark', 'M') }}</span>
    <span class="prime-logo-text">
        <strong>{{ strtolower(config('brand.short', 'MGTEAM')) }}</strong>
        <span>{{ config('brand.tagline', 'FITNESS & HEALTH') }}</span>
    </span>
</a>
