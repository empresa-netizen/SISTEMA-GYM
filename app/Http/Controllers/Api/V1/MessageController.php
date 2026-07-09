<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ConversationResource;
use App\Http\Resources\V1\MessageResource;
use App\Models\Conversation;
use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MessageController extends Controller
{
    public function conversations(Request $request): AnonymousResourceCollection
    {
        $tenantId = $this->tenantId();
        $perPage = min((int) $request->integer('per_page', 20), 100);

        $conversations = Conversation::query()
            ->where('parent_id', $tenantId)
            ->with('member:id,name,email,photo')
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.$request->input('q').'%';
                $query->whereHas('member', fn ($memberQuery) => $memberQuery->where('name', 'like', $term));
            })
            ->orderByDesc('last_message_at')
            ->paginate($perPage);

        return ConversationResource::collection($conversations);
    }

    public function start(Member $member): JsonResponse
    {
        $this->ensureTenantResource($member->parent_id);

        $conversation = Conversation::query()->firstOrCreate(
            [
                'parent_id' => $this->tenantId(),
                'member_id' => $member->id,
            ],
            [
                'last_message_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'Conversa iniciada com sucesso.',
            'data' => new ConversationResource($conversation->load('member:id,name,email,photo')),
        ], 201);
    }

    public function show(Conversation $conversation): ConversationResource
    {
        $this->ensureTenantResource($conversation->parent_id);

        $conversation->load([
            'member:id,name,email,photo',
            'messages',
        ]);

        return new ConversationResource($conversation);
    }

    public function send(Request $request, Conversation $conversation): JsonResponse
    {
        $this->ensureTenantResource($conversation->parent_id);

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
        ]);

        $message = $conversation->messages()->create([
            'sender_type' => 'coach',
            'content' => $validated['content'],
        ]);

        $conversation->update([
            'last_message' => $validated['content'],
            'last_message_at' => now(),
            'unread_by_coach' => false,
        ]);

        return response()->json([
            'message' => 'Mensagem enviada com sucesso.',
            'data' => new MessageResource($message),
        ], 201);
    }

    public function markAsRead(Conversation $conversation): JsonResponse
    {
        $this->ensureTenantResource($conversation->parent_id);

        $conversation->messages()
            ->where('sender_type', 'member')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $conversation->update(['unread_by_coach' => false]);

        return response()->json([
            'message' => 'Mensagens marcadas como lidas.',
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
