<?php

namespace App\Http\Controllers;

use App\Jobs\NotifyFeedInteraction;
use App\Models\CoachFeedItem;
use App\Models\FeedComment;
use App\Models\FeedLike;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedController extends Controller
{
    public function index(): View
    {
        $items = CoachFeedItem::with(['member', 'author', 'comments.user'])
            ->withCount(['likes', 'comments'])
            ->latest()
            ->paginate(20);

        $likedIds = FeedLike::where('user_id', auth()->id())
            ->whereIn('coach_feed_item_id', $items->pluck('id'))
            ->pluck('coach_feed_item_id')
            ->all();

        $summary = [
            'posts' => CoachFeedItem::whereIn('type', ['POST', 'NEWS'])->count(),
            'late' => CoachFeedItem::where('type', 'DELIVERY_LATE')->count(),
            'messages' => CoachFeedItem::where('type', 'CONVERSATION')->count(),
            'feedbacks' => CoachFeedItem::where('type', 'FEEDBACK')->count(),
        ];

        return view('prime.feed.index', compact('items', 'summary', 'likedIds'));
    }

    public function news(): View
    {
        $items = CoachFeedItem::with(['member', 'author', 'comments.user'])
            ->withCount(['likes', 'comments'])
            ->whereIn('type', ['POST', 'NEWS'])
            ->latest()
            ->paginate(20);

        $likedIds = FeedLike::where('user_id', auth()->id())
            ->whereIn('coach_feed_item_id', $items->pluck('id'))
            ->pluck('coach_feed_item_id')
            ->all();

        $summary = [
            'posts' => CoachFeedItem::whereIn('type', ['POST', 'NEWS'])->count(),
            'coach_posts' => CoachFeedItem::whereIn('type', ['POST', 'NEWS'])->whereNull('member_id')->count(),
            'member_posts' => CoachFeedItem::whereIn('type', ['POST', 'NEWS'])->whereNotNull('member_id')->count(),
        ];

        return view('prime.feed.news', compact('items', 'summary', 'likedIds'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'member_id' => 'nullable|exists:members,id',
            'image' => 'nullable|image|max:5120',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('feed', 'public');
        }

        CoachFeedItem::create([
            'parent_id' => parentId(),
            'author_id' => auth()->id(),
            'member_id' => $validated['member_id'] ?? null,
            'type' => 'POST',
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'meta' => 'Publicado pelo coach',
            'image_path' => $imagePath,
            'likes_count' => 0,
            'comments_count' => 0,
        ]);

        return back()->with('success', 'Post publicado no feed.');
    }

    public function like(CoachFeedItem $item): RedirectResponse
    {
        abort_unless($item->parent_id === parentId(), 403);

        $existing = FeedLike::where('coach_feed_item_id', $item->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($existing) {
            $existing->delete();
            $item->update(['likes_count' => max(0, $item->likes()->count())]);

            return back()->with('success', 'Curtida removida.');
        }

        FeedLike::create([
            'parent_id' => parentId(),
            'coach_feed_item_id' => $item->id,
            'user_id' => auth()->id(),
        ]);

        $item->update(['likes_count' => $item->likes()->count()]);

        NotifyFeedInteraction::dispatch($item->id, auth()->id(), 'like');

        return back()->with('success', 'Post curtido.');
    }

    public function comment(Request $request, CoachFeedItem $item): RedirectResponse
    {
        abort_unless($item->parent_id === parentId(), 403);

        $validated = $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        FeedComment::create([
            'parent_id' => parentId(),
            'coach_feed_item_id' => $item->id,
            'user_id' => auth()->id(),
            'body' => $validated['body'],
        ]);

        $item->update(['comments_count' => $item->comments()->count()]);

        NotifyFeedInteraction::dispatch($item->id, auth()->id(), 'comment');

        return back()->with('success', 'Comentário publicado.');
    }
}
