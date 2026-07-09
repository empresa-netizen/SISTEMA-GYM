<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class DashboardCache
{
    public const TTL_SECONDS = 900; // 15 minutos

    public static function apiKey(int $tenantId): string
    {
        return "dashboard:api:v1:{$tenantId}";
    }

    public static function webStatsKey(int $tenantId): string
    {
        return "dashboard:web:stats:{$tenantId}";
    }

    public static function forget(?int $tenantId): void
    {
        if (! $tenantId) {
            return;
        }

        Cache::forget(self::apiKey($tenantId));
        Cache::forget(self::webStatsKey($tenantId));
    }
}
