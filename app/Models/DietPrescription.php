<?php

namespace App\Models;

use App\Traits\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DietPrescription extends Model
{
    use HasTenantScope;

    protected $fillable = [
        'parent_id', 'member_id', 'diet_menu_id', 'title', 'notes',
        'status', 'delivery_status', 'scheduled_at', 'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function dietMenu(): BelongsTo
    {
        return $this->belongsTo(DietMenu::class);
    }
}
