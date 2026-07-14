<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\ClientFeedback;
use App\Models\CoachFeedItem;
use App\Models\CommunityGroup;
use App\Models\CommunityPost;
use App\Models\Conversation;
use App\Models\DietFood;
use App\Models\DietPrescription;
use App\Models\Exercise;
use App\Models\Member;
use App\Models\Workout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfessionalController extends Controller
{
    public function overview(Request $request): JsonResponse
    {
        $tenantId = $this->tenantId($request);
        /** @var \App\Models\User $user */
        $user = $request->user();

        $pendingFeedbacks = ClientFeedback::query()
            ->where('parent_id', $tenantId)
            ->whereIn('status', ['pending', 'PENDING'])
            ->count();

        return response()->json([
            'coach' => [
                'id' => (string) $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'image' => $user->avatar ? url('images/'.$user->avatar) : null,
            ],
            'metrics' => [
                'clients' => Member::query()->where('parent_id', $tenantId)->count(),
                'activeSubscriptions' => Member::query()->where('parent_id', $tenantId)->where('status', 'active')->count(),
                'pendingFeedbacks' => $pendingFeedbacks,
                'unreadConversations' => Conversation::query()->where('parent_id', $tenantId)->where('unread_by_coach', true)->count(),
                'prescriptionsSent' => Workout::query()->where('parent_id', $tenantId)->count(),
            ],
        ]);
    }

    public function clients(Request $request): JsonResponse
    {
        $tenantId = $this->tenantId($request);

        $clients = Member::query()
            ->where('parent_id', $tenantId)
            ->orderBy('name')
            ->get()
            ->map(fn (Member $member) => $this->mapMember($member))
            ->values();

        return response()->json($clients);
    }

    public function showClient(Request $request, int $id): JsonResponse
    {
        $tenantId = $this->tenantId($request);

        $member = Member::query()
            ->where('parent_id', $tenantId)
            ->whereKey($id)
            ->firstOrFail();

        return response()->json($this->mapMember($member));
    }

    public function feedbacks(Request $request): JsonResponse
    {
        $tenantId = $this->tenantId($request);

        $feedbacks = ClientFeedback::query()
            ->where('parent_id', $tenantId)
            ->with('member:id,name,email,photo')
            ->latest()
            ->take(50)
            ->get();

        return response()->json($feedbacks);
    }

    public function conversations(Request $request): JsonResponse
    {
        $tenantId = $this->tenantId($request);

        $conversations = Conversation::query()
            ->where('parent_id', $tenantId)
            ->with('member:id,name,email,photo')
            ->orderByDesc('last_message_at')
            ->take(50)
            ->get();

        return response()->json($conversations);
    }

    public function feed(Request $request): JsonResponse
    {
        $tenantId = $this->tenantId($request);

        $feed = CoachFeedItem::query()
            ->where('parent_id', $tenantId)
            ->with('member:id,name,email,photo')
            ->latest()
            ->take(50)
            ->get();

        return response()->json($feed);
    }

    public function posts(Request $request): JsonResponse
    {
        $tenantId = $this->tenantId($request);

        $posts = CoachFeedItem::query()
            ->where('parent_id', $tenantId)
            ->with('member:id,name,email,photo')
            ->latest()
            ->take(50)
            ->get();

        return response()->json($posts);
    }

    public function community(Request $request): JsonResponse
    {
        $tenantId = $this->tenantId($request);

        $groups = CommunityGroup::query()
            ->where('parent_id', $tenantId)
            ->withCount('posts')
            ->with([
                'posts' => fn ($query) => $query
                    ->with('member:id,name,email,photo')
                    ->latest()
                    ->limit(5),
            ])
            ->latest()
            ->take(50)
            ->get();

        $recentPosts = CommunityPost::query()
            ->where('parent_id', $tenantId)
            ->with(['group:id,parent_id,name', 'member:id,name,email,photo'])
            ->latest()
            ->take(30)
            ->get();

        return response()->json([
            'groups' => $groups->map(fn (CommunityGroup $group) => $this->mapCommunityGroup($group))->values(),
            'recent_posts' => $recentPosts->map(fn (CommunityPost $post) => $this->mapCommunityPost($post))->values(),
        ]);
    }

    public function prescriptions(Request $request): JsonResponse
    {
        $tenantId = $this->tenantId($request);

        $workouts = Workout::query()
            ->where('parent_id', $tenantId)
            ->with(['member:id,name,email', 'activities'])
            ->latest()
            ->take(50)
            ->get();

        $diets = DietPrescription::query()
            ->where('parent_id', $tenantId)
            ->with(['member:id,name,email', 'dietMenu:id,name,status'])
            ->latest()
            ->take(50)
            ->get();

        return response()->json([
            'workouts' => $workouts,
            'diets' => $diets,
        ]);
    }

    public function catalogExercises(Request $request): JsonResponse
    {
        $tenantId = $this->tenantId($request);

        $exercises = Exercise::query()
            ->where('parent_id', $tenantId)
            ->orderBy('name')
            ->get();

        return response()->json($exercises);
    }

    public function catalogFoods(Request $request): JsonResponse
    {
        $tenantId = $this->tenantId($request);

        $foods = DietFood::query()
            ->where('parent_id', $tenantId)
            ->orderBy('name')
            ->get();

        return response()->json($foods);
    }

    private function tenantId(Request $request): int
    {
        return (int) (parentId() ?? $request->user()?->id);
    }

    private function mapMember(Member $member): array
    {
        return [
            'id' => (string) $member->id,
            'name' => (string) ($member->name ?? ''),
            'email' => $member->email,
            'photoUrl' => $member->photo,
            'image' => $member->photo,
            'phone' => $member->phone,
            'whatsapp' => $member->phone,
            'gender' => $member->gender,
            'status' => $member->status,
            'adherence' => [
                'workout' => true,
                'diet' => true,
            ],
            'activeSubscription' => null,
            'xp' => 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapCommunityGroup(CommunityGroup $group): array
    {
        return [
            'id' => $group->id,
            'name' => $group->name,
            'description' => $group->description,
            'members_count' => $group->members_count,
            'posts_count' => $group->posts_count ?? $group->posts()->count(),
            'created_at' => $group->created_at?->toIso8601String(),
            'posts' => $group->posts
                ->map(fn (CommunityPost $post) => $this->mapCommunityPost($post))
                ->values(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapCommunityPost(CommunityPost $post): array
    {
        $member = $post->member;

        return [
            'id' => $post->id,
            'group_id' => $post->community_group_id,
            'group_name' => $post->group?->name,
            'member_id' => $post->member_id,
            'author_name' => $member?->name ?? 'Coach',
            'author_image' => $member?->photo ? asset('storage/'.$member->photo) : null,
            'content' => $post->content,
            'likes_count' => $post->likes_count,
            'created_at' => $post->created_at?->toIso8601String(),
        ];
    }
}
