<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'parent_id',
        'invoice_number',
        'member_id',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'status',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    /**
     * Boot method to auto-generate invoice number
     */
    protected static function boot()
    {
        parent::boot();

        // Apply tenant scoping for data isolation
        static::addGlobalScope(new \App\Models\Scopes\TenantScope);

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateInvoiceNumber($invoice->parent_id);
            }
        });
    }

    /**
     * Generate unique invoice number
     */
    public static function generateInvoiceNumber($parentId = null): string
    {
        $parentId = $parentId ?? parentId();
        $lastInvoice = self::where('parent_id', $parentId)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice && preg_match('/#INV-(\d+)/', $lastInvoice->invoice_number, $matches)) {
            $number = intval($matches[1]) + 1;
        } else {
            $number = 1;
        }

        return '#INV-'.str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the owner/parent
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    /**
     * Get the member
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Get invoice items
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get invoice payments
     */
    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class);
    }

    /**
     * Get remaining balance
     */
    public function getRemainingBalanceAttribute(): float
    {
        return $this->total_amount - $this->paid_amount;
    }

    /**
     * Check if invoice is fully paid
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid' || $this->paid_amount >= $this->total_amount;
    }

    /**
     * Check if invoice is overdue
     */
    public function isOverdue(): bool
    {
        if ($this->status === 'paid' || $this->status === 'cancelled') {
            return false;
        }

        return $this->status === 'overdue'
            || ($this->due_date && $this->due_date->isPast());
    }

    /**
     * Update invoice status based on payments
     */
    public function updateStatus(): void
    {
        if ($this->paid_amount >= $this->total_amount) {
            $this->status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partially_paid';
        } elseif ($this->due_date && $this->due_date->isPast()) {
            $this->status = 'overdue';
        } else {
            $this->status = 'unpaid';
        }
        $this->save();
    }

    /**
     * Calculate totals from items
     */
    public function calculateTotals(): void
    {
        $this->subtotal = $this->items()->sum('total_price');
        $this->total_amount = $this->subtotal + $this->tax_amount - $this->discount_amount;
        $this->save();
    }

    /**
     * Scope for overdue invoices
     */
    public function scopeOverdue($query)
    {
        return $query->where(function ($builder) {
            $builder->where('status', 'overdue')
                ->orWhere(function ($open) {
                    $open->whereIn('status', ['unpaid', 'partially_paid'])
                        ->where('due_date', '<', now());
                });
        });
    }

    /**
     * Scope for paid invoices
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope for unpaid invoices
     */
    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    public function getActionButtonsAttribute()
    {
        $buttons = '';

        // View button - check permission
        $buttons .= $this->view($this);

        // Edit button - check permission
        if (auth()->user()->can('edit payments')) {
            $buttons .= $this->edit($this);
        }

        // Delete button - check permission
        if (auth()->user()->can('delete payments')) {
            $buttons .= $this->deleteModel(route('invoices.destroy', $this), csrf_token(), 'invoice-table');
        }

        return '<div class="d-inline-block">'
            .'<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false"><i class="ri-more-2-fill fs-17"></i></a>'
            .'<ul class="dropdown-menu dropdown-menu-end m-0">'
            .$buttons
            .'</ul></div>';
    }

    public function edit($customer)
    {
        return '<li><a href="'.route('invoices.edit', $customer).'" class="dropdown-item">Edit</a></li>';
    }

    public function view($customer)
    {
        return '<li><a href="'.route('invoices.show', $customer).'" class="dropdown-item">View</a></li>';
    }

    public function deleteModel($route, $token, $dataTableId)
    {
        return '<li><a href="#" onclick="deleteRow(`'.$route.'`,`'.$token.'`'.',`'.$dataTableId.'`'.')" title="Delete" class="dropdown-item text-danger">Delete</a></li>';
    }
}
