@php
    $variant = $variant ?? 'auto'; // auto | light | dark
    $size = $size ?? 'md'; // sm | md | lg

    $isDarkSurface = $variant === 'dark'
        || ($variant === 'auto');

    $logoSrc = $isDarkSurface
        ? asset('brand/mgteam-branco.svg')
        : asset('brand/mgteam-preto.svg');

    $sizes = [
        'sm' => '2.6rem',
        'md' => '4rem',
        'lg' => '6.5rem',
    ];
    $height = $sizes[$size] ?? $sizes['md'];
@endphp
<a href="{{ auth()->check() ? route('dashboard') : route('welcome') }}" class="prime-logo prime-logo--{{ $size }}">
    <img
        src="{{ $logoSrc }}"
        alt="{{ config('brand.name', 'MGTEAM FITNESS & HEALTH') }}"
        class="prime-logo-img"
        style="height: {{ $height }}; width: auto;"
    >
</a>
