<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Lab404\Impersonate\Models\Impersonate;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Impersonate, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'parent_id',
        'parent_id',
        'subscription',
        'subscription_expire_date',
        'twofa_enabled',
        'twofa_secret',
        'code',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'twofa_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'subscription_expire_date' => 'datetime',
        'twofa_enabled' => 'boolean',
    ];

    /**
     * Canal privado para notificações em tempo real (Reverb).
     */
    public function receivesBroadcastNotificationsOn(): string
    {
        return 'user.'.$this->id;
    }

    /**
     * Get logged histories for this user
     */
    public function loggedHistories()
    {
        return $this->hasMany(LoggedHistory::class);
    }

    /**
     * Check if the user can impersonate others.
     *
     * @return bool
     */
    public function canImpersonate()
    {
        return $this->hasRole('super-admin'); // Only super-admin can impersonate
    }

    /**
     * Check if the user can be impersonated.
     *
     * @return bool
     */
    public function canBeImpersonated()
    {
        return ! $this->hasRole('super-admin'); // Super-admin cannot be impersonated
    }

    /**
     * Get the tenant for this user (if they are an owner).
     */
    public function tenant()
    {
        return $this->hasOne(Tenant::class, 'user_id');
    }

    public function getActionButtonsAttribute()
    {
        $buttons = '';

        // View button - check permission
        if ($this->can('view users')) {
            $buttons .= $this->view($this);
        }

        // Edit button - check permission
        if ($this->can('edit users')) {
            $buttons .= $this->edit($this);
        }

        // Delete button - check permission
        if ($this->can('delete users')) {
            $buttons .= $this->deleteModel(route('users.destroy', $this), csrf_token(), 'user-table');
        }

        if (auth()->user()->canImpersonate() && $this->canBeImpersonated() && $this->id != auth()->id()) {
            $buttons .= ' <a href="'.route('impersonate', $this).'" class="link-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="Impersonate">
                                                   Impersonate
                                                </a>';
        }

        // If no buttons are visible, return empty
        if (empty($buttons)) {
            return '';
        }

        return '<div class="d-inline-block">'
            .'<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false"><i class="ri-more-2-fill fs-17"></i></a>'
            .'<ul class="dropdown-menu dropdown-menu-end m-0">'
            .$buttons
            .'</ul></div>';
    }

    public function edit($customer)
    {
        return '<li><a href="'.route('users.edit', $customer).'" class="dropdown-item">Edit</a></li>';
    }

    public function view($customer)
    {
        return '<li><a href="'.route('users.show', $customer).'" class="dropdown-item">View</a></li>';
    }

    public function deleteModel($route, $token, $dataTableId)
    {
        return '<li><a href="#" onclick="deleteRow(`'.$route.'`,`'.$token.'`'.',`'.$dataTableId.'`'.')" title="Delete" class="dropdown-item text-danger">Delete</a></li>';
    }
}
