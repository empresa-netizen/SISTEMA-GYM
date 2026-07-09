<?php

namespace App\Http\Controllers;

use App\Http\Requests\MembershipPlanStoreRequest;
use App\Http\Requests\MembershipPlanUpdateRequest;
use App\Models\MembershipPlan;
use Illuminate\Http\Request;

class MembershipPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $parentId = parentId();

        $query = MembershipPlan::query()
            ->where('parent_id', $parentId)
            ->withCount('members')
            ->latest();

        if ($search = trim((string) $request->get('search_value', $request->get('search', '')))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('period')) {
            $query->where('duration_type', $request->period);
        }

        if ($request->filled('service')) {
            if ($request->service === 'training') {
                $query->where('personal_training', true);
            } elseif ($request->service === 'standard') {
                $query->where('personal_training', false);
            }
        }

        if ($request->filled('includes')) {
            if ($request->includes === 'classes') {
                $query->whereNotNull('max_classes');
            } elseif ($request->includes === 'unlimited') {
                $query->whereNull('max_classes');
            }
        }

        if ($request->filled('recurrence')) {
            if ($request->recurrence === 'recurring') {
                $query->where('duration_type', '!=', 'lifetime');
            } elseif ($request->recurrence === 'single') {
                $query->where('duration_type', 'lifetime');
            }
        }

        $plans = $query->get();
        $activeCount = MembershipPlan::where('parent_id', $parentId)->where('is_active', true)->count();
        $inactiveCount = MembershipPlan::where('parent_id', $parentId)->where('is_active', false)->count();
        $totalCount = $activeCount + $inactiveCount;

        return view('membership-plans.index', compact('plans', 'activeCount', 'inactiveCount', 'totalCount'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('membership-plans.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MembershipPlanStoreRequest $request)
    {
        $data = $request->validated();
        $data['parent_id'] = parentId();

        // Handle unchecked checkboxes - boolean() returns false if missing
        $data['personal_training'] = $request->boolean('personal_training');
        $data['is_active'] = $request->boolean('is_active');

        MembershipPlan::create($data);

        return redirect()->route('membership-plans.index')
            ->with('success', 'Plano criado com sucesso');
    }

    /**
     * Display the specified resource.
     */
    public function show(MembershipPlan $membershipPlan)
    {
        if ($membershipPlan->parent_id != parentId()) {
            abort(403);
        }

        return view('membership-plans.show', compact('membershipPlan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MembershipPlan $membershipPlan)
    {
        if ($membershipPlan->parent_id != parentId()) {
            abort(403);
        }

        return view('membership-plans.edit', compact('membershipPlan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MembershipPlanUpdateRequest $request, MembershipPlan $membershipPlan)
    {
        if ($membershipPlan->parent_id != parentId()) {
            abort(403);
        }

        $data = $request->validated();
        // Handle unchecked checkboxes - boolean() returns false if missing
        $data['personal_training'] = $request->boolean('personal_training');
        $data['is_active'] = $request->boolean('is_active');

        $membershipPlan->update($data);

        return redirect()->route('membership-plans.index')
            ->with('success', 'Plano atualizado com sucesso');
    }

    public function duplicate(MembershipPlan $membershipPlan)
    {
        if ($membershipPlan->parent_id != parentId()) {
            abort(403);
        }

        $copy = $membershipPlan->replicate();
        $copy->name = $membershipPlan->name.' (cópia)';
        $copy->is_active = false;
        $copy->save();

        return redirect()->route('membership-plans.edit', $copy)
            ->with('success', 'Plano duplicado. Revise os dados antes de publicar.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MembershipPlan $membershipPlan)
    {
        if ($membershipPlan->parent_id != parentId()) {
            abort(403);
        }

        $membershipPlan->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Plano excluído com sucesso',
            ]);
        }

        return redirect()->route('membership-plans.index')
            ->with('success', 'Plano excluído com sucesso');
    }
}
