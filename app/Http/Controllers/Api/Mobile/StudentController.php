<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\ClientFeedback;
use App\Models\CoachFeedItem;
use App\Models\Conversation;
use App\Models\DietFood;
use App\Models\DietPrescription;
use App\Models\Exercise;
use App\Models\Member;
use App\Models\MemberLogbook;
use App\Models\MemberPhoto;
use App\Models\Workout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function me(Request $request): JsonResponse
    {
        $member = $this->resolveClientMember($request);
        if (! $member) {
            return response()->json([
                'message' => 'Cliente nao identificado para este token.',
            ], 401);
        }

        return response()->json([
            'id' => (string) $member->id,
            'name' => (string) ($member->name ?? ''),
            'email' => $member->email,
            'image' => $member->photo,
            'phone' => $member->phone,
            'status' => $member->status,
        ]);
    }

    public function prescriptions(Request $request): JsonResponse
    {
        $member = $this->resolveClientMember($request);
        if (! $member) {
            return response()->json(['workouts' => [], 'diets' => []]);
        }

        $workouts = Workout::query()
            ->where('parent_id', $member->parent_id)
            ->where('member_id', $member->id)
            ->with('activities')
            ->latest()
            ->take(50)
            ->get();

        $diets = DietPrescription::query()
            ->where('parent_id', $member->parent_id)
            ->where('member_id', $member->id)
            ->with('dietMenu:id,name,status')
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
        $member = $this->resolveClientMember($request);
        if (! $member) {
            return response()->json([]);
        }

        $exercises = Exercise::query()
            ->where('parent_id', $member->parent_id)
            ->orderBy('name')
            ->get();

        return response()->json($exercises);
    }

    public function catalogFoods(Request $request): JsonResponse
    {
        $member = $this->resolveClientMember($request);
        if (! $member) {
            return response()->json([]);
        }

        $foods = DietFood::query()
            ->where('parent_id', $member->parent_id)
            ->orderBy('name')
            ->get();

        return response()->json($foods);
    }

    public function feed(Request $request): JsonResponse
    {
        $member = $this->resolveClientMember($request);
        if (! $member) {
            return response()->json([]);
        }

        $feed = CoachFeedItem::query()
            ->where('parent_id', $member->parent_id)
            ->where(function ($query) use ($member) {
                $query->whereNull('member_id')->orWhere('member_id', $member->id);
            })
            ->latest()
            ->take(50)
            ->get();

        return response()->json($feed);
    }

    public function feedbacks(Request $request): JsonResponse
    {
        $member = $this->resolveClientMember($request);
        if (! $member) {
            return response()->json([]);
        }

        $feedbacks = ClientFeedback::query()
            ->where('parent_id', $member->parent_id)
            ->where('member_id', $member->id)
            ->latest()
            ->take(50)
            ->get();

        return response()->json($feedbacks);
    }

    public function messagesConversation(Request $request): JsonResponse
    {
        $member = $this->resolveClientMember($request);
        if (! $member) {
            return response()->json([]);
        }

        $conversation = Conversation::query()
            ->where('parent_id', $member->parent_id)
            ->where('member_id', $member->id)
            ->with('messages')
            ->latest('last_message_at')
            ->first();

        if (! $conversation) {
            return response()->json([]);
        }

        return response()->json($conversation);
    }

    public function logbooks(Request $request): JsonResponse
    {
        $member = $this->resolveClientMember($request);
        if (! $member) {
            return response()->json([]);
        }

        $logbooks = MemberLogbook::query()
            ->where('parent_id', $member->parent_id)
            ->where('member_id', $member->id)
            ->latest('logged_at')
            ->take(100)
            ->get();

        return response()->json($logbooks);
    }

    public function photos(Request $request): JsonResponse
    {
        $member = $this->resolveClientMember($request);
        if (! $member) {
            return response()->json([]);
        }

        $photos = MemberPhoto::query()
            ->where('parent_id', $member->parent_id)
            ->where('member_id', $member->id)
            ->latest()
            ->take(100)
            ->get();

        return response()->json($photos);
    }

    public function engagement(Request $request): JsonResponse
    {
        $member = $this->resolveClientMember($request);
        if (! $member) {
            return response()->json([
                'logbooks' => 0,
                'photos' => 0,
                'feedbacks' => 0,
            ]);
        }

        return response()->json([
            'logbooks' => MemberLogbook::query()->where('parent_id', $member->parent_id)->where('member_id', $member->id)->count(),
            'photos' => MemberPhoto::query()->where('parent_id', $member->parent_id)->where('member_id', $member->id)->count(),
            'feedbacks' => ClientFeedback::query()->where('parent_id', $member->parent_id)->where('member_id', $member->id)->count(),
        ]);
    }

    public function groups(): JsonResponse
    {
        return response()->json([]);
    }

    private function resolveClientMember(Request $request): ?Member
    {
        return app(MobileAuthController::class)->resolveClientMemberFromToken($request);
    }
}
