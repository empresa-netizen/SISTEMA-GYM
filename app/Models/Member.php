<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Member extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'parent_id',
        'user_id',
        'member_id',
        'name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'medical_conditions',
        'photo',
        'membership_plan_id',
        'membership_start_date',
        'membership_end_date',
        'status',
        'coach_user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'membership_start_date' => 'date',
            'membership_end_date' => 'date',
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

        static::creating(function ($member) {
            if (empty($member->member_id)) {
                $member->member_id = self::generateMemberId($member->parent_id);
            }
        });
    }

    /**
     * Generate unique member ID
     */
    public static function generateMemberId($parentId = null): string
    {
        $parentId = $parentId ?? parentId();
        // Check globally to ensure uniqueness since member_id is unique in DB
        $lastMember = self::orderBy('id', 'desc')
            ->first();

        if ($lastMember && preg_match('/#MBR-(\d+)/', $lastMember->member_id, $matches)) {
            $number = intval($matches[1]) + 1;
        } else {
            $number = 1;
        }

        return '#MBR-'.str_pad($number, 4, '0', STR_PAD_LEFT);
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
     * Get the membership plan
     */
    public function membershipPlan(): BelongsTo
    {
        return $this->belongsTo(MembershipPlan::class);
    }

    /**
     * Get subscriptions
     */
    public function subscriptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function paymentTransactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function workouts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Workout::class);
    }

    public function anamnesis(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MemberAnamnesis::class);
    }

    public function photos(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MemberPhoto::class)->latest();
    }

    public function logbooks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MemberLogbook::class)->latest('logged_at');
    }

    public function dietPrescriptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DietPrescription::class)->latest();
    }

    public function cardioPlans(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CardioPlan::class)->latest();
    }

    public function memberNotes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MemberNote::class)->latest('noted_at');
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_user_id');
    }

    public function feedbacks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ClientFeedback::class)->latest();
    }

    public function healthRecords(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Health::class)->latest('measurement_date');
    }

    public function appointments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Event::class)->orderBy('start_time');
    }

    public function conversation(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Conversation::class);
    }

    /**
     * Check if membership is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' &&
               (! $this->membership_end_date || $this->membership_end_date->isFuture());
    }

    /**
     * Check if membership is expired
     */
    public function isExpired(): bool
    {
        return $this->membership_end_date && $this->membership_end_date->isPast();
    }

    /**
     * Scope to get only active members
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get expired members
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
            ->orWhere(function ($q) {
                $q->whereNotNull('membership_end_date')
                    ->where('membership_end_date', '<', now());
            });
    }

    public function getActionButtonsAttribute()
    {
        $buttons = '';

        // View button - check permission
        if (auth()->user()->can('view members')) {
            $buttons .= $this->view($this);
        }

        // Edit button - check permission
        if (auth()->user()->can('edit members')) {
            $buttons .= $this->edit($this);
        }

        // Delete button - check permission
        if (auth()->user()->can('delete members')) {
            $buttons .= $this->deleteModel(route('members.destroy', $this), csrf_token(), 'member-table');
        }

        return '<div class="d-inline-block">'
            .'<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false"><i class="ri-more-2-fill fs-17"></i></a>'
            .'<ul class="dropdown-menu dropdown-menu-end m-0">'
            .$buttons
            .'</ul></div>';
    }

    public function edit($customer)
    {
        return '<li><a href="'.route('members.edit', $customer).'" class="dropdown-item">Edit</a></li>';
    }

    public function view($customer)
    {
        return '<li><a href="'.route('members.show', $customer).'" class="dropdown-item">View</a></li>';
    }

    public function deleteModel($route, $token, $dataTableId)
    {
        return '<li><a href="#" onclick="deleteRow(`'.$route.'`,`'.$token.'`'.',`'.$dataTableId.'`'.')" title="Delete" class="dropdown-item text-danger">Delete</a></li>';
    }
}
