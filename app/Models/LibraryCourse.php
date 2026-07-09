<?php

namespace App\Models;

use App\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryCourse extends Model
{
    use HasTenantScope;

    protected $fillable = [
        'parent_id',
        'title',
        'product',
        'modules_count',
        'lessons_count',
        'status',
        'description',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }
}
