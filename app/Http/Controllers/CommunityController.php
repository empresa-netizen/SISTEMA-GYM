<?php

namespace App\Http\Controllers;

use App\Models\CommunityGroup;
use App\Models\CommunityPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunityController extends Controller
{
    public function index(): View
    {
        $groups = CommunityGroup::withCount('posts')->latest()->get();
        $recentPosts = CommunityPost::with(['group', 'member'])
            ->latest()
            ->paginate(10);
        $summary = [
            'groups' => $groups->count(),
            'members' => $groups->sum('members_count'),
            'posts' => $recentPosts->total(),
            'likes' => CommunityPost::sum('likes_count'),
            'reported' => 0,
            'comments' => 0,
        ];

        return view('mgteam.community.index', compact('groups', 'recentPosts', 'summary'));
    }

    public function show(CommunityGroup $group): View
    {
        abort_unless($group->parent_id === parentId(), 403);
        $group->load(['posts.member']);

        return view('mgteam.community.show', compact('group'));
    }

    public function storeGroup(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        CommunityGroup::create([
            'parent_id' => parentId(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return back()->with('success', 'Grupo criado.');
    }

    public function storePost(Request $request, CommunityGroup $group): RedirectResponse
    {
        abort_unless($group->parent_id === parentId(), 403);

        $validated = $request->validate(['content' => 'required|string|max:5000']);

        CommunityPost::create([
            'parent_id' => parentId(),
            'community_group_id' => $group->id,
            'content' => $validated['content'],
        ]);

        return back()->with('success', 'Post publicado.');
    }
}
