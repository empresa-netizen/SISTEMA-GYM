<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trainer extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'parent_id',
        'user_id',
        'trainer_id',
        'name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'address',
        'photo',
        'bio',
        'specializations',
        'certifications',
        'years_of_experience',
        'hourly_rate',
        'status',
        'availability',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'specializations' => 'array',
            'certifications' => 'array',
            'availability' => 'array',
            'hourly_rate' => 'decimal:2',
            'years_of_experience' => 'integer',
        ];
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Apply tenant scoping for data isolation
        static::addGlobalScope(new \App\Models\Scopes\TenantScope);

        static::creating(function ($trainer) {
            if (empty($trainer->trainer_id)) {
                $trainer->trainer_id = self::generateTrainerId($trainer->parent_id);
            }
        });
    }

    /**
     * Generate unique trainer ID
     */
    public static function generateTrainerId($parentId = null): string
    {
        $lastTrainer = self::withoutGlobalScopes()
            ->whereNotNull('trainer_id')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastTrainer && preg_match('/#TRNR-(\d+)/', $lastTrainer->trainer_id, $matches)) {
            $number = intval($matches[1]) + 1;
        } else {
            $number = 1;
        }

        return '#TRNR-'.str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the owner/parent
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    /**
     * Get the associated user account
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if trainer is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Scope to get only active trainers
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get formatted hourly rate
     */
    public function getFormattedHourlyRateAttribute(): string
    {
        return $this->hourly_rate ? '$'.number_format($this->hourly_rate, 2) : 'N/A';
    }

    public function getActionButtonsAttribute()
    {
        $buttons = '';

        // View button - check permission
        if (auth()->user()->can('view trainers')) {
            $buttons .= $this->view($this);
        }

        // Edit button - check permission
        if (auth()->user()->can('edit trainers')) {
            $buttons .= $this->edit($this);
        }

        // Delete button - check permission
        if (auth()->user()->can('delete trainers')) {
            $buttons .= $this->deleteModel(route('trainers.destroy', $this), csrf_token(), 'trainer-table');
        }

        return '<div class="d-inline-block">'
            .'<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false"><i class="ri-more-2-fill fs-17"></i></a>'
            .'<ul class="dropdown-menu dropdown-menu-end m-0">'
            .$buttons
            .'</ul></div>';
    }

    public function edit($customer)
    {
        return '<li><a href="'.route('trainers.edit', $customer).'" class="dropdown-item">Edit</a></li>';
    }

    public function view($customer)
    {
        return '<li><a href="'.route('trainers.show', $customer).'" class="dropdown-item">View</a></li>';
    }

    public function deleteModel($route, $token, $dataTableId)
    {
        return '<li><a href="#" onclick="deleteRow(`'.$route.'`,`'.$token.'`'.',`'.$dataTableId.'`'.')" title="Delete" class="dropdown-item text-danger">Delete</a></li>';
    }
}
