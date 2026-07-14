<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreWorkoutPrescriptionRequest;
use App\Http\Resources\V1\DietPrescriptionResource;
use App\Http\Resources\V1\MemberResource;
use App\Http\Resources\V1\WorkoutResource;
use App\Models\DietMenu;
use App\Models\DietPrescription;
use App\Models\Member;
use App\Models\Workout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PrescriptionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenantId = $this->tenantId();
        $memberId = $request->integer('member_id');

        $workouts = Workout::query()
            ->where('parent_id', $tenantId)
            ->with(['member:id,name', 'activities'])
            ->when($memberId, fn ($query) => $query->where('member_id', $memberId))
            ->latest()
            ->paginate(min((int) $request->integer('per_page', 20), 100), ['*'], 'workouts_page');

        $diets = DietPrescription::query()
            ->where('parent_id', $tenantId)
            ->with(['member:id,name', 'dietMenu:id,name,status'])
            ->when($memberId, fn ($query) => $query->where('member_id', $memberId))
            ->latest()
            ->paginate(min((int) $request->integer('per_page', 20), 100), ['*'], 'diets_page');

        return response()->json([
            'data' => [
                'workouts' => [
                    'data' => WorkoutResource::collection($workouts->getCollection())->resolve(),
                    'meta' => [
                        'current_page' => $workouts->currentPage(),
                        'last_page' => $workouts->lastPage(),
                        'per_page' => $workouts->perPage(),
                        'total' => $workouts->total(),
                    ],
                ],
                'diets' => [
                    'data' => DietPrescriptionResource::collection($diets->getCollection())->resolve(),
                    'meta' => [
                        'current_page' => $diets->currentPage(),
                        'last_page' => $diets->lastPage(),
                        'per_page' => $diets->perPage(),
                        'total' => $diets->total(),
                    ],
                ],
            ],
        ]);
    }

    public function member(Member $member): JsonResponse
    {
        $this->ensureTenantResource($member->parent_id);

        $workouts = $member->workouts()->with('activities')->latest()->paginate(20, ['*'], 'workouts_page');
        $diets = $member->dietPrescriptions()->with('dietMenu')->latest()->paginate(20, ['*'], 'diets_page');

        return response()->json([
            'data' => [
                'member' => (new MemberResource($member))->resolve(),
                'workouts' => [
                    'data' => WorkoutResource::collection($workouts->getCollection())->resolve(),
                    'meta' => [
                        'current_page' => $workouts->currentPage(),
                        'last_page' => $workouts->lastPage(),
                        'per_page' => $workouts->perPage(),
                        'total' => $workouts->total(),
                    ],
                ],
                'diets' => [
                    'data' => DietPrescriptionResource::collection($diets->getCollection())->resolve(),
                    'meta' => [
                        'current_page' => $diets->currentPage(),
                        'last_page' => $diets->lastPage(),
                        'per_page' => $diets->perPage(),
                        'total' => $diets->total(),
                    ],
                ],
            ],
        ]);
    }

    public function storeDiet(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'member_id' => ['required', 'exists:members,id'],
            'diet_menu_id' => ['nullable', 'exists:diet_menus,id'],
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['draft', 'scheduled', 'sent'])],
            'delivery_status' => ['nullable', Rule::in(['PENDING', 'DELIVERED', 'LATE'])],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        $member = Member::query()->findOrFail($validated['member_id']);
        $this->ensureTenantResource($member->parent_id);
        $dietMenuId = null;

        if (! empty($validated['diet_menu_id'])) {
            $dietMenuId = DietMenu::query()
                ->whereKey($validated['diet_menu_id'])
                ->firstOrFail()
                ->id;
        }

        $diet = DietPrescription::query()->create([
            'parent_id' => $this->tenantId(),
            'member_id' => $member->id,
            'diet_menu_id' => $dietMenuId,
            'title' => $validated['title'],
            'notes' => $validated['notes'] ?? null,
            'status' => $validated['status'] ?? 'draft',
            'delivery_status' => $validated['delivery_status'] ?? 'PENDING',
            'scheduled_at' => $validated['scheduled_at'] ?? null,
        ]);

        return response()->json([
            'message' => 'Prescricao alimentar criada com sucesso.',
            'data' => new DietPrescriptionResource($diet->load(['member:id,name', 'dietMenu:id,name,status'])),
        ], 201);
    }

    public function storeWorkout(StoreWorkoutPrescriptionRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $member = Member::query()->findOrFail($validated['member_id']);
        $this->ensureTenantResource($member->parent_id);

        $workout = DB::transaction(function () use ($validated, $member) {
            $workout = Workout::query()->create([
                'parent_id' => $this->tenantId(),
                'member_id' => $member->id,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'workout_date' => $validated['workout_date'] ?? null,
                'status' => $validated['status'] ?? 'active',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['activities'] as $index => $activity) {
                $workout->activities()->create([
                    'exercise_name' => $activity['exercise_name'],
                    'description' => $activity['description'] ?? null,
                    'sets' => $activity['sets'] ?? null,
                    'reps' => $activity['reps'] ?? null,
                    'duration_minutes' => $activity['duration_minutes'] ?? null,
                    'rest_seconds' => $activity['rest_seconds'] ?? 60,
                    'weight_kg' => $activity['weight_kg'] ?? null,
                    'order' => $activity['order'] ?? $index + 1,
                    'notes' => $activity['notes'] ?? null,
                ]);
            }

            return $workout->load(['member:id,name', 'activities.logs']);
        });

        return response()->json([
            'message' => 'Prescricao de treino criada com sucesso.',
            'data' => new WorkoutResource($workout),
        ], 201);
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
