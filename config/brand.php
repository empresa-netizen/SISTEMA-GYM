<?php

return [
    'name' => env('BRAND_NAME', 'MGTEAM FITNESS & HEALTH'),
    'short' => env('BRAND_SHORT', 'MGTEAM'),
    'tagline' => env('BRAND_TAGLINE', 'SAÚDE · ESTÉTICA · PERFORMANCE'),
    'pay' => env('BRAND_PAY', 'MGTEAM Pay'),
    'logo_mark' => env('BRAND_LOGO_MARK', 'M'),
    'support_email' => env('BRAND_SUPPORT_EMAIL', 'suporte@mgteam.app'),
    'slogan' => env('BRAND_SLOGAN', 'Acompanhamento que trata você por inteiro.'),
    'handle' => env('BRAND_HANDLE', '@mgteamoficial'),
    'colors' => [
        'sage' => '#A8CDB7',
        'dark' => '#24332D',
        'cream' => '#F6F5F0',
        'neutral' => '#CFC9BE',
        'mid' => '#6B746D',
        'mark' => '#727E67',
    ],
    'apps' => [
        'pro_url' => env('APP_PRO_URL', 'http://localhost:8089'),
        'student_url' => env('APP_STUDENT_URL', 'http://localhost:8086'),
    ],
];
