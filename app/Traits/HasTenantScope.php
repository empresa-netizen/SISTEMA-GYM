<?php

namespace App\Traits;

use App\Models\Scopes\TenantScope;

trait HasTenantScope
{
    protected static function bootHasTenantScope(): void
    {
        static::addGlobalScope(new TenantScope);
    }
}
