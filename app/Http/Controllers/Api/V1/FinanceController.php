<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\InvoicePaymentResource;
use App\Http\Resources\V1\InvoiceResource;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function dashboard(): JsonResponse
    {
        $tenantId = $this->tenantId();

        $paymentsQuery = InvoicePayment::query()
            ->whereHas('invoice', fn ($query) => $query->where('parent_id', $tenantId));

        $recentPayments = (clone $paymentsQuery)
            ->with('invoice.member:id,name')
            ->latest()
            ->take(8)
            ->get();

        $data = [
            'available_balance' => (float) (clone $paymentsQuery)->sum('amount'),
            'pending_balance' => (float) Invoice::query()
                ->where('parent_id', $tenantId)
                ->whereIn('status', ['unpaid', 'partially_paid'])
                ->sum(DB::raw('total_amount - paid_amount')),
            'month_revenue' => (float) (clone $paymentsQuery)
                ->where('payment_date', '>=', now()->startOfMonth())
                ->sum('amount'),
            'month_transactions' => (clone $paymentsQuery)
                ->where('payment_date', '>=', now()->startOfMonth())
                ->count(),
            'recent_payments' => InvoicePaymentResource::collection($recentPayments),
        ];

        return response()->json(['data' => $data]);
    }

    public function invoices(Request $request): AnonymousResourceCollection
    {
        $tenantId = $this->tenantId();
        $perPage = min((int) $request->integer('per_page', 20), 100);

        $invoices = Invoice::query()
            ->where('parent_id', $tenantId)
            ->with(['member:id,name,email', 'payments'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('member_id'), fn ($query) => $query->where('member_id', (int) $request->member_id))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->input('q').'%';
                $query->where(function ($subQuery) use ($term) {
                    $subQuery->where('invoice_number', 'like', $term)
                        ->orWhereHas('member', fn ($memberQuery) => $memberQuery->where('name', 'like', $term));
                });
            })
            ->latest('invoice_date')
            ->paginate($perPage);

        return InvoiceResource::collection($invoices);
    }

    public function invoice(Invoice $invoice): InvoiceResource
    {
        $this->ensureTenantResource($invoice->parent_id);

        $invoice->load(['member', 'items', 'payments']);

        return new InvoiceResource($invoice);
    }

    public function payments(Request $request): AnonymousResourceCollection
    {
        $tenantId = $this->tenantId();
        $perPage = min((int) $request->integer('per_page', 20), 100);

        $payments = InvoicePayment::query()
            ->whereHas('invoice', fn ($query) => $query->where('parent_id', $tenantId))
            ->with('invoice.member:id,name')
            ->when($request->filled('method'), fn ($query) => $query->where('payment_method', $request->input('method')))
            ->latest('payment_date')
            ->paginate($perPage);

        return InvoicePaymentResource::collection($payments);
    }

    private function tenantId(): int
    {
        return (int) (parentId() ?? auth()->id());
    }

    private function ensureTenantResource(?int $resourceParentId): void
    {
        abort_if((int) $resourceParentId !== $this->tenantId(), 403, 'Acesso nao autorizado.');
    }
}
