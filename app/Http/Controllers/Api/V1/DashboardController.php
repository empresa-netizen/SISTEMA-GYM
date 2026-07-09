<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\EventResource;
use App\Http\Resources\V1\FeedPostResource;
use App\Http\Resources\V1\InvoicePaymentResource;
use App\Models\ClientFeedback;
use App\Models\CoachFeedItem;
use App\Models\Conversation;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Member;
use App\Support\DashboardCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $tenantId = $this->tenantId();

        $payload = Cache::remember(
            DashboardCache::apiKey($tenantId),
            DashboardCache::TTL_SECONDS,
            fn () => $this->buildDashboardPayload($tenantId)
        );

        return response()->json(['data' => $payload]);
    }

    private function buildDashboardPayload(int $tenantId): array
    {
        $membersQuery = Member::query()->where('parent_id', $tenantId);
        $invoicesQuery = Invoice::query()->where('parent_id', $tenantId);
        $paymentsQuery = InvoicePayment::query()
            ->whereHas('invoice', fn ($query) => $query->where('parent_id', $tenantId));

        $recentFeed = CoachFeedItem::query()
            ->where('parent_id', $tenantId)
            ->with('member:id,name')
            ->latest()
            ->take(5)
            ->get();

        $recentPayments = (clone $paymentsQuery)
            ->with('invoice.member:id,name')
            ->latest()
            ->take(5)
            ->get();

        $recentEvents = Event::query()
            ->where('parent_id', $tenantId)
            ->where('start_time', '>=', now()->startOfDay())
            ->orderBy('start_time')
            ->take(5)
            ->get();

        return [
            'kpis' => [
                'members_total' => (clone $membersQuery)->count(),
                'members_active' => (clone $membersQuery)->where('status', 'active')->count(),
                'events_upcoming' => Event::query()
                    ->where('parent_id', $tenantId)
                    ->where('start_time', '>', now())
                    ->count(),
                'conversations_unread' => Conversation::query()
                    ->where('parent_id', $tenantId)
                    ->where('unread_by_coach', true)
                    ->count(),
                'feedback_pending' => ClientFeedback::query()
                    ->where('parent_id', $tenantId)
                    ->where('status', 'pending')
                    ->count(),
                'invoices_open' => (clone $invoicesQuery)
                    ->whereIn('status', ['unpaid', 'partially_paid'])
                    ->count(),
                'revenue_month' => (float) (clone $paymentsQuery)
                    ->whereDate('payment_date', '>=', now()->startOfMonth())
                    ->sum('amount'),
            ],
            'recent' => [
                'feed' => FeedPostResource::collection($recentFeed)->resolve(),
                'payments' => InvoicePaymentResource::collection($recentPayments)->resolve(),
                'events' => EventResource::collection($recentEvents)->resolve(),
            ],
            'cached_until' => now()->addSeconds(DashboardCache::TTL_SECONDS)->toIso8601String(),
        ];
    }

    private function tenantId(): int
    {
        return (int) (parentId() ?? auth()->id());
    }
}
