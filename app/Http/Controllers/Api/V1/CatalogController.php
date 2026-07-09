<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\DietMenuResource;
use App\Http\Resources\V1\ExerciseResource;
use App\Http\Resources\V1\MembershipPlanResource;
use App\Models\DietMenu;
use App\Models\Exercise;
use App\Models\MembershipPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CatalogController extends Controller
{
    public function overview(): JsonResponse
    {
        $tenantId = $this->tenantId();

        return response()->json([
            'data' => [
                'exercises_total' => Exercise::query()->where('parent_id', $tenantId)->count(),
                'diet_menus_total' => DietMenu::query()->where('parent_id', $tenantId)->count(),
                'membership_plans_total' => MembershipPlan::query()->where('parent_id', $tenantId)->count(),
                'membership_plans_active' => MembershipPlan::query()->where('parent_id', $tenantId)->where('is_active', true)->count(),
            ],
        ]);
    }

    public function exercises(Request $request): AnonymousResourceCollection
    {
        $tenantId = $this->tenantId();
        $perPage = min((int) $request->integer('per_page', 30), 100);

        $exercises = Exercise::query()
            ->where('parent_id', $tenantId)
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where('name', 'like', '%'.$request->input('q').'%');
            })
            ->orderBy('name')
            ->paginate($perPage);

        return ExerciseResource::collection($exercises);
    }

    public function dietMenus(Request $request): AnonymousResourceCollection
    {
        $tenantId = $this->tenantId();
        $perPage = min((int) $request->integer('per_page', 30), 100);

        $menus = DietMenu::query()
            ->where('parent_id', $tenantId)
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->orderBy('name')
            ->paginate($perPage);

        return DietMenuResource::collection($menus);
    }

    public function membershipPlans(Request $request): AnonymousResourceCollection
    {
        $tenantId = $this->tenantId();
        $perPage = min((int) $request->integer('per_page', 30), 100);

        $plans = MembershipPlan::query()
            ->where('parent_id', $tenantId)
            ->when($request->boolean('active_only'), fn ($query) => $query->where('is_active', true))
            ->orderBy('name')
            ->paginate($perPage);

        return MembershipPlanResource::collection($plans);
    }

    private function tenantId(): int
    {
        return (int) (parentId() ?? auth()->id());
    }
}
