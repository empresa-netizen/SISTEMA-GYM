<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\EventResource;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $tenantId = $this->tenantId();
        $perPage = min((int) $request->integer('per_page', 20), 100);

        $events = Event::query()
            ->where('parent_id', $tenantId)
            ->with('member:id,name,email')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('member_id'), fn ($query) => $query->where('member_id', (int) $request->member_id))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('start_time', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('start_time', '<=', $request->input('to')))
            ->orderBy('start_time')
            ->paginate($perPage);

        return EventResource::collection($events);
    }

    public function show(Event $event): EventResource
    {
        $this->ensureTenantResource($event->parent_id);

        return new EventResource($event->load('member:id,name,email'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'member_id' => ['nullable', 'exists:members,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'location' => ['nullable', 'string', 'max:255'],
            'max_participants' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', Rule::in(['scheduled', 'ongoing', 'completed', 'cancelled'])],
        ]);

        $event = Event::query()->create([
            'parent_id' => $this->tenantId(),
            'member_id' => $validated['member_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'location' => $validated['location'] ?? null,
            'max_participants' => $validated['max_participants'] ?? null,
            'registered_count' => 0,
            'status' => $validated['status'] ?? 'scheduled',
        ]);

        return response()->json([
            'message' => 'Evento criado com sucesso.',
            'data' => new EventResource($event->load('member:id,name,email')),
        ], 201);
    }

    public function update(Request $request, Event $event): JsonResponse
    {
        $this->ensureTenantResource($event->parent_id);

        $validated = $request->validate([
            'member_id' => ['sometimes', 'nullable', 'exists:members,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'start_time' => ['sometimes', 'date'],
            'end_time' => ['sometimes', 'date'],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'max_participants' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'registered_count' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', Rule::in(['scheduled', 'ongoing', 'completed', 'cancelled'])],
        ]);

        if (isset($validated['start_time'], $validated['end_time']) && strtotime($validated['end_time']) <= strtotime($validated['start_time'])) {
            return response()->json([
                'message' => 'O campo end_time deve ser posterior ao start_time.',
                'errors' => [
                    'end_time' => ['O campo end_time deve ser posterior ao start_time.'],
                ],
            ], 422);
        }

        $event->update($validated);

        return response()->json([
            'message' => 'Evento atualizado com sucesso.',
            'data' => new EventResource($event->fresh()->load('member:id,name,email')),
        ]);
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
