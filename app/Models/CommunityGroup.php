<?php

namespace App\Models;

use App\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunityGroup extends Model
{
    use HasTenantScope;

    protected $fillable = ['parent_id', 'name', 'description', 'members_count'];

    public function posts(): HasMany
    {
        return $this->hasMany(CommunityPost::class)->latest();
    }
}
