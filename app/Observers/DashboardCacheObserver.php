<?php

namespace App\Observers;

use App\Support\DashboardCache;
use Illuminate\Database\Eloquent\Model;

class DashboardCacheObserver
{
    public function saved(Model $model): void
    {
        DashboardCache::forget($this->tenantId($model));
    }

    public function deleted(Model $model): void
    {
        DashboardCache::forget($this->tenantId($model));
    }

    private function tenantId(Model $model): ?int
    {
        if (isset($model->parent_id) && $model->parent_id) {
            return (int) $model->parent_id;
        }

        if (method_exists($model, 'invoice') && $model->relationLoaded('invoice') && $model->invoice) {
            return (int) $model->invoice->parent_id;
        }

        if (method_exists($model, 'invoice') && isset($model->invoice_id)) {
            $invoice = $model->invoice()->select('id', 'parent_id')->first();

            return $invoice ? (int) $invoice->parent_id : null;
        }

        return null;
    }
}
