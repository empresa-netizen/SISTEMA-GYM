<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ClientFeedbackResource;
use App\Http\Resources\V1\MemberResource;
use App\Http\Resources\V1\WorkoutResource;
use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $tenantId = $this->tenantId();
        $perPage = min((int) $request->integer('per_page', 15), 100);

        $members = Member::query()
            ->where('parent_id', $tenantId)
            ->with('membershipPlan:id,name,price,duration_type,duration_value')
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where(function ($subQuery) use ($request) {
                    $term = '%'.$request->input('q').'%';
                    $subQuery->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('member_id', 'like', $term);
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->latest()
            ->paginate($perPage);

        return MemberResource::collection($members);
    }

    public function show(int $member): MemberResource
    {
        // Resolve sem TenantScope para distinguir 403 (outro tenant) de 404 (inexistente).
        $model = Member::withoutGlobalScopes()->findOrFail($member);
        $this->ensureTenantResource($model->parent_id);

        $model->load([
            'membershipPlan',
            'conversation',
            'anamnesis',
            'healthRecords',
            'photos',
            'feedbacks',
            'dietPrescriptions.dietMenu',
        ]);

        return new MemberResource($model);
    }

    public function update(Request $request, Member $member): JsonResponse
    {
        $this->ensureTenantResource($member->parent_id);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', Rule::unique('members', 'email')->ignore($member->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(['active', 'inactive', 'expired', 'suspended'])],
        ]);

        $noteBody = $validated['notes'] ?? null;
        unset($validated['notes']);

        $member->update($validated);

        if (filled($noteBody)) {
            \App\Models\MemberNote::create([
                'parent_id' => $member->parent_id,
                'member_id' => $member->id,
                'author_id' => auth()->id(),
                'body' => $noteBody,
                'noted_at' => now(),
            ]);
        }

        return response()->json([
            'message' => 'Cliente atualizado com sucesso.',
            'data' => new MemberResource($member->fresh(['membershipPlan', 'memberNotes'])),
        ]);
    }

    public function workouts(Member $member): AnonymousResourceCollection
    {
        $this->ensureTenantResource($member->parent_id);

        $workouts = $member->workouts()
            ->with('activities')
            ->latest()
            ->paginate(20);

        return WorkoutResource::collection($workouts);
    }

    public function feedbacks(Member $member): AnonymousResourceCollection
    {
        $this->ensureTenantResource($member->parent_id);

        $feedbacks = $member->feedbacks()->paginate(20);

        return ClientFeedbackResource::collection($feedbacks);
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
