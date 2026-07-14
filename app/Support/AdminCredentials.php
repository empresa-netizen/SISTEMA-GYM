<?php

namespace App\Support;

use Illuminate\Support\Str;

class AdminCredentials
{
    public const CANONICAL_EMAIL = 'coach@mgteam.app';

    public const LEGACY_ADMIN_EMAIL = 'admin@mgteam.app';

    public const LEGACY_COACH_EMAIL = 'coach@mgteam.local';

    /**
     * @var array<string, string>
     */
    private const EMAIL_ALIASES = [
        self::LEGACY_ADMIN_EMAIL => self::CANONICAL_EMAIL,
        self::LEGACY_COACH_EMAIL => self::CANONICAL_EMAIL,
    ];

    public static function resolveEmail(string $email): string
    {
        $normalizedEmail = Str::lower(trim($email));

        return self::EMAIL_ALIASES[$normalizedEmail] ?? $normalizedEmail;
    }
}
