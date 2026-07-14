<?php

namespace App\Models;

use App\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberPhoto extends Model
{
    use HasTenantScope;

    protected $fillable = ['parent_id', 'member_id', 'path', 'type', 'caption'];

    protected $appends = ['url'];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/'.$this->path);
    }
}
