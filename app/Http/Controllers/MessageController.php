<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Member;
use App\Models\MembershipPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(Request $request): View
    {
        $parentId = parentId();
        $status = $request->get('status', 'all');

        $plans = MembershipPlan::query()
            ->where('parent_id', $parentId)
            ->orderBy('name')
            ->get();

        $conversations = Conversation::query()
            ->with('member.membershipPlan')
            ->when($request->q, function ($query, $search) {
                $query->whereHas('member', function ($memberQuery) use ($search) {
                    $memberQuery->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('member_id', 'like', '%'.$search.'%');
                });
            })
            ->when($request->product, fn ($query, $product) => $query->whereHas('member', fn ($memberQuery) => $memberQuery->where('membership_plan_id', $product)))
            ->when($status === 'unread', fn ($query) => $query->where('unread_by_coach', true))
            ->when($status === 'read', fn ($query) => $query->where('unread_by_coach', false))
            ->when($status === 'closed', fn ($query) => $query->whereHas('member', fn ($memberQuery) => $memberQuery->whereIn('status', ['inactive', 'expired', 'suspended'])))
            ->orderByDesc('last_message_at')
            ->get();

        $active = $request->conversation
            ? Conversation::with(['member.membershipPlan', 'messages'])->findOrFail($request->conversation)
            : $conversations->first();

        if ($active) {
            $active->messages()->where('sender_type', 'member')->whereNull('read_at')->update(['read_at' => now()]);
            $active->update(['unread_by_coach' => false]);
            $conversations->firstWhere('id', $active->id)?->forceFill(['unread_by_coach' => false]);
        }

        return view('prime.messages.index', compact('conversations', 'active', 'plans', 'status'));
    }

    public function store(Request $request, Conversation $conversation): RedirectResponse
    {
        abort_unless($conversation->parent_id === parentId(), 403);

        $validated = $request->validate(['content' => 'required|string|max:5000']);

        app(\App\Services\ChatMessenger::class)->sendFromCoach($conversation, $validated['content']);

        return redirect()->route('messages.index', ['conversation' => $conversation->id]);
    }

    public function start(Member $member): RedirectResponse
    {
        abort_unless($member->parent_id === parentId(), 403);

        $conversation = Conversation::firstOrCreate(
            ['parent_id' => parentId(), 'member_id' => $member->id],
            ['last_message_at' => now()]
        );

        return redirect()->route('messages.index', ['conversation' => $conversation->id]);
    }
}
