<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Mobile\StoreStudentCommunityPostRequest;
use App\Http\Requests\Api\Mobile\StoreStudentFeedbackRequest;
use App\Http\Requests\Api\Mobile\StoreStudentPhotoRequest;
use App\Http\Resources\V1\DietPrescriptionResource;
use App\Http\Resources\V1\WorkoutResource;
use App\Models\ClientFeedback;
use App\Models\CoachFeedItem;
use App\Models\CommunityGroup;
use App\Models\CommunityPost;
use App\Models\Conversation;
use App\Models\DietFood;
use App\Models\DietMeal;
use App\Models\DietPrescription;
use App\Models\Exercise;
use App\Models\Member;
use App\Models\MemberLogbook;
use App\Models\MemberPhoto;
use App\Models\Workout;
use App\Models\WorkoutActivity;
use App\Services\ChatMessenger;
use App\Services\DietMacroAuditor;
use App\Services\WorkoutSessionLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class StudentController extends Controller
{
    private const DIET_MEAL_LOGBOOK_SOURCES = [
        'student_diet_meal_complete',
        'student_diet_detail_meal',
    ];

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
            ->with('activities.logs')
            ->withCount([
                'activities as activities_total',
                'activities as activities_completed' => fn ($query) => $query->where('is_completed', true),
            ])
            ->latest()
            ->take(50)
            ->get();

        $diets = DietPrescription::query()
            ->where('parent_id', $member->parent_id)
            ->where('member_id', $member->id)
            ->with('dietMenu.meals.mealFoods.dietFood')
            ->latest()
            ->take(50)
            ->get();

        return response()->json([
            'workouts' => WorkoutResource::collection($workouts)->resolve($request),
            'diets' => DietPrescriptionResource::collection($diets)->resolve($request),
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

    public function storeFeedback(StoreStudentFeedbackRequest $request): JsonResponse
    {
        $member = $this->resolveClientMember($request);
        if (! $member) {
            return response()->json(['message' => 'Cliente nao identificado.'], 401);
        }

        $validated = $request->validated();
        $contextType = $validated['context_type'] ?? 'general';
        $contextId = $validated['context_id'] ?? null;
        $contextPrefix = $contextType !== 'general'
            ? '['.strtoupper($contextType).($contextId ? " #{$contextId}" : '').'] '
            : '';

        $feedback = ClientFeedback::query()->create([
            'parent_id' => $member->parent_id,
            'member_id' => $member->id,
            'status' => 'pending',
            'message' => $contextPrefix.$validated['message'],
            'rating' => $validated['rating'] ?? null,
            'context_type' => $contextType,
            'context_id' => $contextId,
        ]);

        return response()->json([
            'message' => 'Feedback enviado para o coach.',
            'data' => $feedback,
        ], 201);
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

    public function sendMessage(Request $request, ChatMessenger $messenger): JsonResponse
    {
        $member = $this->resolveClientMember($request);
        if (! $member) {
            return response()->json(['message' => 'Cliente nao identificado.'], 401);
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
        ]);

        $result = $messenger->sendFromMember($member, $validated['content']);

        return response()->json([
            'message' => 'Mensagem enviada.',
            'data' => $result['message'],
            'conversation_id' => $result['conversation']->id,
        ], 201);
    }

    public function markMessagesRead(Request $request, ChatMessenger $messenger): JsonResponse
    {
        $member = $this->resolveClientMember($request);
        if (! $member) {
            return response()->json(['message' => 'Cliente nao identificado.'], 401);
        }

        $conversation = Conversation::query()
            ->where('parent_id', $member->parent_id)
            ->where('member_id', $member->id)
            ->latest('last_message_at')
            ->first();

        if (! $conversation) {
            return response()->json(['message' => 'Nenhuma conversa encontrada.', 'updated' => 0]);
        }

        $updated = $messenger->markCoachMessagesRead($conversation);

        return response()->json([
            'message' => 'Mensagens marcadas como lidas.',
            'updated' => $updated,
        ]);
    }

    public function logWorkoutActivity(
        Request $request,
        Workout $workout,
        WorkoutActivity $activity,
        WorkoutSessionLogger $logger
    ): JsonResponse {
        $member = $this->resolveClientMember($request);
        if (! $member) {
            return response()->json(['message' => 'Cliente nao identificado.'], 401);
        }

        abort_unless((int) $workout->member_id === (int) $member->id, 403);
        abort_unless((int) $workout->parent_id === (int) $member->parent_id, 403);
        abort_unless((int) $activity->workout_id === (int) $workout->id, 404);

        $validated = $request->validate([
            'is_completed' => ['sometimes', 'boolean'],
            'sets' => ['nullable', 'integer', 'min:0', 'max:50'],
            'reps' => ['nullable', 'integer', 'min:0', 'max:500'],
            'weight_kg' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $log = $logger->logActivity($workout, $activity, $validated);
        $updatedWorkout = $workout->fresh('activities.logs');

        return response()->json([
            'message' => 'Exercicio registrado.',
            'data' => $log->load('activity'),
            'workout' => (new WorkoutResource($updatedWorkout))->resolve($request),
        ], 201);
    }

    public function uncompleteWorkoutActivity(
        Request $request,
        Workout $workout,
        WorkoutActivity $activity,
        WorkoutSessionLogger $logger
    ): JsonResponse {
        $member = $this->resolveClientMember($request);
        if (! $member) {
            return response()->json(['message' => 'Cliente nao identificado.'], 401);
        }

        abort_unless((int) $workout->member_id === (int) $member->id, 403);
        abort_unless((int) $workout->parent_id === (int) $member->parent_id, 403);
        abort_unless((int) $activity->workout_id === (int) $workout->id, 404);

        $updatedWorkout = $logger->uncompleteActivity($workout, $activity);

        return response()->json([
            'message' => 'Exercicio desmarcado.',
            'workout' => (new WorkoutResource($updatedWorkout))->resolve($request),
        ]);
    }

    public function completeWorkout(
        Request $request,
        Workout $workout,
        WorkoutSessionLogger $logger
    ): JsonResponse {
        $member = $this->resolveClientMember($request);
        if (! $member) {
            return response()->json(['message' => 'Cliente nao identificado.'], 401);
        }

        abort_unless((int) $workout->member_id === (int) $member->id, 403);
        abort_unless((int) $workout->parent_id === (int) $member->parent_id, 403);

        $validated = $request->validate([
            'comment' => ['nullable', 'string', 'max:2000'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'duration_seconds' => ['nullable', 'integer', 'min:0', 'max:86400'],
        ]);

        $completed = $logger->completeWorkout(
            $workout,
            $validated['comment'] ?? null,
            $validated['rating'] ?? null,
            $validated['duration_seconds'] ?? null,
        );
        $completedResource = (new WorkoutResource($completed))->resolve($request);
        $logbook = MemberLogbook::query()
            ->where('parent_id', $member->parent_id)
            ->where('member_id', $member->id)
            ->where('type', 'TRAINING')
            ->whereDate('logged_at', today())
            ->where('metadata->source', 'student_workout_complete')
            ->where('metadata->workout_id', $workout->id)
            ->first();

        return response()->json([
            'message' => 'Treino concluido.',
            'data' => $completedResource,
            'workout' => $completedResource,
            'logbook' => $logbook,
        ]);
    }

    public function dietPrint(Request $request, DietPrescription $prescription, DietMacroAuditor $auditor): View|JsonResponse
    {
        $member = $this->resolveClientMember($request);
        if (! $member) {
            return response()->json(['message' => 'Cliente nao identificado.'], 401);
        }

        abort_unless((int) $prescription->member_id === (int) $member->id, 403);
        abort_unless((int) $prescription->parent_id === (int) $member->parent_id, 403);

        return $this->renderDietPrint($prescription, $auditor, $member);
    }

    public function dietPrintLink(Request $request, DietPrescription $prescription): JsonResponse
    {
        $member = $this->resolveClientMember($request);
        if (! $member) {
            return response()->json(['message' => 'Cliente nao identificado.'], 401);
        }

        abort_unless((int) $prescription->member_id === (int) $member->id, 403);
        abort_unless((int) $prescription->parent_id === (int) $member->parent_id, 403);

        $expiresAt = now()->addMinutes(30);

        return response()->json([
            'url' => URL::temporarySignedRoute('mobile.student.diets.print', $expiresAt, [
                'prescription' => $prescription->id,
            ]),
            'expires_at' => $expiresAt->toISOString(),
        ]);
    }

    public function signedDietPrint(DietPrescription $prescription, DietMacroAuditor $auditor): View
    {
        return $this->renderDietPrint($prescription, $auditor);
    }

    public function completeDietMeal(
        Request $request,
        DietPrescription $prescription,
        DietMeal $meal
    ): JsonResponse {
        $member = $this->resolveClientMember($request);
        if (! $member) {
            return response()->json(['message' => 'Cliente nao identificado.'], 401);
        }

        abort_unless((int) $prescription->member_id === (int) $member->id, 403);
        abort_unless((int) $prescription->parent_id === (int) $member->parent_id, 403);
        abort_unless((int) $meal->diet_menu_id === (int) $prescription->diet_menu_id, 404);

        $validated = $request->validate([
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);
        $meal->loadMissing('mealFoods.dietFood');
        $macros = $meal->computedMacros();

        $existingLogbook = MemberLogbook::query()
            ->where('parent_id', $member->parent_id)
            ->where('member_id', $member->id)
            ->where('type', 'DIET')
            ->whereDate('logged_at', today())
            ->whereIn('metadata->source', self::DIET_MEAL_LOGBOOK_SOURCES)
            ->where('metadata->prescription_id', $prescription->id)
            ->where('metadata->meal_id', $meal->id)
            ->first();

        if ($existingLogbook) {
            return response()->json([
                'message' => 'Refeicao ja registrada hoje.',
                'data' => $existingLogbook,
                'created' => false,
            ]);
        }

        $logbook = MemberLogbook::query()->create([
            'parent_id' => $member->parent_id,
            'member_id' => $member->id,
            'type' => 'DIET',
            'title' => 'Refeicao concluida: '.$meal->name,
            'logged_at' => now(),
            'numeric_value' => $macros['calories'],
            'unit' => 'kcal',
            'metadata' => [
                'source' => 'student_diet_meal_complete',
                'prescription_id' => $prescription->id,
                'meal_id' => $meal->id,
                'meal_name' => $meal->name,
                'macros' => $macros,
            ],
            'comment' => $validated['comment'] ?? 'Refeicao marcada como concluida pelo app do aluno.',
        ]);

        return response()->json([
            'message' => 'Refeicao registrada.',
            'data' => $logbook,
            'created' => true,
        ], 201);
    }

    public function uncompleteDietMeal(
        Request $request,
        DietPrescription $prescription,
        DietMeal $meal
    ): JsonResponse {
        $member = $this->resolveClientMember($request);
        if (! $member) {
            return response()->json(['message' => 'Cliente nao identificado.'], 401);
        }

        abort_unless((int) $prescription->member_id === (int) $member->id, 403);
        abort_unless((int) $prescription->parent_id === (int) $member->parent_id, 403);
        abort_unless((int) $meal->diet_menu_id === (int) $prescription->diet_menu_id, 404);

        $deleted = MemberLogbook::query()
            ->where('parent_id', $member->parent_id)
            ->where('member_id', $member->id)
            ->where('type', 'DIET')
            ->whereDate('logged_at', today())
            ->whereIn('metadata->source', self::DIET_MEAL_LOGBOOK_SOURCES)
            ->where('metadata->prescription_id', $prescription->id)
            ->where('metadata->meal_id', $meal->id)
            ->delete();

        return response()->json([
            'message' => $deleted > 0 ? 'Refeicao desmarcada.' : 'Refeicao ja estava desmarcada.',
            'deleted' => $deleted,
            'meal_id' => $meal->id,
            'prescription_id' => $prescription->id,
        ]);
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

    public function storeLogbook(Request $request): JsonResponse
    {
        $member = $this->resolveClientMember($request);
        if (! $member) {
            return response()->json(['message' => 'Cliente nao identificado.'], 401);
        }

        $validated = $request->validate([
            'type' => ['required', 'in:TRAINING,DIET,WEIGHT'],
            'title' => ['required', 'string', 'max:255'],
            'logged_at' => ['nullable', 'date'],
            'date' => ['nullable', 'date'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'numeric_value' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'unit' => ['nullable', 'string', 'max:20'],
            'metadata' => ['nullable', 'array'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $logbook = MemberLogbook::query()->create([
            'parent_id' => $member->parent_id,
            'member_id' => $member->id,
            'type' => $validated['type'],
            'title' => $validated['title'],
            'logged_at' => $validated['logged_at'] ?? $validated['date'] ?? now(),
            'rating' => $validated['rating'] ?? null,
            'numeric_value' => $validated['numeric_value'] ?? null,
            'unit' => $validated['unit'] ?? null,
            'metadata' => $validated['metadata'] ?? null,
            'comment' => $validated['comment'] ?? null,
        ]);

        return response()->json([
            'message' => 'Registro salvo no diario.',
            'data' => $logbook,
        ], 201);
    }

    private function renderDietPrint(
        DietPrescription $prescription,
        DietMacroAuditor $auditor,
        ?Member $member = null
    ): View {
        $summary = $auditor->summarize($prescription);
        $prescription->loadMissing('dietMenu', 'member');

        return view('mgteam.diets.print', [
            'prescription' => $prescription,
            'summary' => $summary,
            'member' => $member ?? $prescription->member,
        ]);
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

    public function storePhoto(StoreStudentPhotoRequest $request): JsonResponse
    {
        $member = $this->resolveClientMember($request);
        if (! $member) {
            return response()->json(['message' => 'Cliente nao identificado.'], 401);
        }

        $validated = $request->validated();
        $path = $request->file('photo')->store('member-photos', 'public');

        $photo = MemberPhoto::query()->create([
            'parent_id' => $member->parent_id,
            'member_id' => $member->id,
            'path' => $path,
            'type' => $validated['type'] ?? 'progress',
            'caption' => $validated['caption'] ?? null,
        ]);

        return response()->json([
            'message' => 'Foto de evolucao enviada.',
            'data' => $photo,
        ], 201);
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

    public function groups(Request $request): JsonResponse
    {
        $member = $this->resolveClientMember($request);
        if (! $member) {
            return response()->json([
                'groups' => [],
                'recent_posts' => [],
            ]);
        }

        $groups = CommunityGroup::query()
            ->where('parent_id', $member->parent_id)
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
            ->where('parent_id', $member->parent_id)
            ->with(['group:id,parent_id,name', 'member:id,name,email,photo'])
            ->latest()
            ->take(30)
            ->get();

        return response()->json([
            'groups' => $groups->map(fn (CommunityGroup $group) => $this->mapCommunityGroup($group))->values(),
            'recent_posts' => $recentPosts->map(fn (CommunityPost $post) => $this->mapCommunityPost($post))->values(),
        ]);
    }

    public function storeGroupPost(StoreStudentCommunityPostRequest $request, int $group): JsonResponse
    {
        $member = $this->resolveClientMember($request);
        if (! $member) {
            return response()->json(['message' => 'Cliente nao identificado.'], 401);
        }

        $communityGroup = CommunityGroup::query()
            ->where('parent_id', $member->parent_id)
            ->whereKey($group)
            ->firstOrFail();

        $post = CommunityPost::query()->create([
            'parent_id' => $member->parent_id,
            'community_group_id' => $communityGroup->id,
            'member_id' => $member->id,
            'content' => $request->validated('content'),
        ])->load(['group:id,parent_id,name', 'member:id,name,email,photo']);

        return response()->json([
            'message' => 'Publicacao enviada para a comunidade.',
            'data' => $this->mapCommunityPost($post),
        ], 201);
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

    private function resolveClientMember(Request $request): ?Member
    {
        return app(MobileAuthController::class)->resolveClientMemberFromToken($request);
    }
}
