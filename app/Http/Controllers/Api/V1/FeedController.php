<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ClientFeedbackResource;
use App\Http\Resources\V1\FeedPostResource;
use App\Models\ClientFeedback;
use App\Models\CoachFeedItem;
use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class FeedController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = $this->tenantId();
        $perPage = min((int) $request->integer('per_page', 20), 100);

        $items = CoachFeedItem::query()
            ->where('parent_id', $tenantId)
            ->with('member:id,name,email')
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->input('type')))
            ->latest()
            ->paginate($perPage);

        $summary = [
            'posts' => CoachFeedItem::query()->where('parent_id', $tenantId)->where('type', 'POST')->count(),
            'late' => CoachFeedItem::query()->where('parent_id', $tenantId)->where('type', 'DELIVERY_LATE')->count(),
            'messages' => CoachFeedItem::query()->where('parent_id', $tenantId)->where('type', 'CONVERSATION')->count(),
            'feedbacks' => CoachFeedItem::query()->where('parent_id', $tenantId)->where('type', 'FEEDBACK')->count(),
        ];

        return FeedPostResource::collection($items)->additional([
            'summary' => $summary,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'member_id' => ['nullable', 'exists:members,id'],
            'type' => ['nullable', Rule::in(['POST', 'DELIVERY_LATE', 'CONVERSATION', 'FEEDBACK'])],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'meta' => ['nullable', 'string', 'max:255'],
        ]);

        $memberId = $this->resolveTenantMemberId($validated['member_id'] ?? null);

        $item = CoachFeedItem::query()->create([
            'parent_id' => $this->tenantId(),
            'author_id' => auth()->id(),
            'member_id' => $memberId,
            'type' => $validated['type'] ?? 'POST',
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'meta' => $validated['meta'] ?? 'Publicado pelo coach',
        ]);

        return response()->json([
            'message' => 'Item publicado no feed.',
            'data' => new FeedPostResource($item->load('member:id,name,email')),
        ], 201);
    }

    public function feedbacks(Request $request): AnonymousResourceCollection
    {
        $tenantId = $this->tenantId();
        $perPage = min((int) $request->integer('per_page', 20), 100);

        $feedbacks = ClientFeedback::query()
            ->where('parent_id', $tenantId)
            ->with('member:id,name,email')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->latest()
            ->paginate($perPage);

        return ClientFeedbackResource::collection($feedbacks);
    }

    private function tenantId(): int
    {
        return (int) (parentId() ?? auth()->id());
    }

    private function resolveTenantMemberId(null|int|string $memberId): ?int
    {
        if (! $memberId) {
            return null;
        }

        return Member::query()
            ->whereKey($memberId)
            ->firstOrFail()
            ->id;
    }
}
