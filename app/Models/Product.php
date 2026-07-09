<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'parent_id',
        'category_id',
        'product_id',
        'name',
        'type',
        'description',
        'sku',
        'price',
        'cost_price',
        'stock_quantity',
        'min_stock_level',
        'unit',
        'image',
        'active',
        'track_inventory',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'active' => 'boolean',
        'track_inventory' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        // Apply tenant scoping for data isolation
        static::addGlobalScope(new \App\Models\Scopes\TenantScope);

        static::creating(function ($product) {
            if (empty($product->product_id)) {
                $product->product_id = self::generateProductId($product->parent_id);
            }
        });
    }

    public static function generateProductId($parentId = null): string
    {
        $parentId = $parentId ?? parentId();
        $lastProduct = self::withoutGlobalScopes()
            ->where('parent_id', $parentId)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastProduct && preg_match('/#PRD-(\d+)/', $lastProduct->product_id, $matches)) {
            $number = intval($matches[1]) + 1;
        } else {
            $number = 1;
        }

        return '#PRD-'.str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(ProductSale::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->min_stock_level;
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'min_stock_level');
    }

    public function getActionButtonsAttribute()
    {

        return '<div class="d-inline-block">'
            . '<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false"><i class="ri-more-2-fill fs-17"></i></a>'
            . '<ul class="dropdown-menu dropdown-menu-end m-0">'
            . $this->view($this)
            . $this->edit($this)
            .  $this->deleteModel(route("products.destroy", $this), csrf_token(), "workout-table")
            . '</ul></div>';
    }

    public function edit($customer)
    {
        return '<li><a href="' . route('products.edit', $customer) . '" class="dropdown-item">Edit</a></li>';
    }

    public function view($customer)
    {
        return '<li><a href="' . route('products.show', $customer) . '" class="dropdown-item">View</a></li>';
    }

    public function deleteModel($route, $token, $dataTableId)
    {
        return '<li><a href="#" onclick="deleteRow(`' . $route . '`,`' . $token . '`' . ',`' . $dataTableId . '`' . ')" title="Delete" class="dropdown-item text-danger">Delete</a></li>';
    }
}
