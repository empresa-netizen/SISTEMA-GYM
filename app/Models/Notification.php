<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $table = 'notification_templates';

    protected $fillable = [
        'parent_id',
        'module',
        'subject',
        'message',
        'enabled_email',
        'enabled_web',
    ];

    protected $casts = [
        'enabled_email' => 'boolean',
        'enabled_web' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }
}
