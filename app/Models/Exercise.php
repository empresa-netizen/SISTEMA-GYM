<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Exercise extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'vimeo_id',
        'vimeo_url',
        'embed_url',
        'duration_seconds',
        'source',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new Scopes\TenantScope);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }
}
