<?php

namespace App\Http\Controllers;

use App\Http\Requests\WorkoutPrescriptionRequest;
use App\Models\Member;
use App\Models\Trainer;
use App\Models\Workout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WorkoutController extends Controller
{
    /**
     * Display a listing of workouts
     */
    public function index(Request $request): View
    {
        $parentId = parentId();

        $query = Workout::query()
            ->where('parent_id', $parentId)
            ->with(['member', 'trainer', 'activities'])
            ->latest();

        if ($search = trim((string) $request->get('search_value', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('workout_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('member')) {
            $query->where('member_id', $request->member);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $workouts = $query->paginate(20)->withQueryString();
        $members = Member::where('parent_id', $parentId)->orderBy('name')->get();

        return view('workouts.index', compact('workouts', 'members'));
    }

    /**
     * Show the form for creating a new workout
     */
    public function create(): View
    {
        $parentId = parentId();
        $members = Member::where('parent_id', $parentId)->active()->get();
        $trainers = Trainer::where('parent_id', $parentId)->active()->get();

        return view('workouts.create', compact('members', 'trainers'));
    }

    public function store(WorkoutPrescriptionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $activities = $validated['activities'] ?? [];
        unset($validated['activities']);

        $validated['parent_id'] = parentId();

        $workout = DB::transaction(function () use ($activities, $validated) {
            $workout = Workout::create($validated);

            foreach (array_values($activities) as $index => $activity) {
                $workout->activities()->create(array_merge($activity, [
                    'order' => $index,
                ]));
            }

            return $workout;
        });

        // Send email if workout assigned to member
        if ($workout->member_id) {
            $member = $workout->member;
            sendNotificationEmail('workout_create', $member->email, [
                'gym_name' => settings('app_name', 'FitHub'),
                'member_name' => $member->name,
                'workout_id' => $workout->workout_id,
                'duration' => $workout->workout_date ? $workout->workout_date : 'Not specified',
            ]);
        }

        return $this->redirectAfterPersist($workout, 'Treino salvo com sucesso: '.$workout->workout_id);
    }

    /**
     * Display the specified workout
     */
    public function show(Workout $workout): View
    {
        // Check multi-tenant isolation
        if ($workout->parent_id != parentId()) {
            abort(403, 'Unauthorized access');
        }

        $workout->load(['member', 'trainer', 'activities']);

        return view('workouts.show', compact('workout'));
    }

    /**
     * Show the form for editing the specified workout
     */
    public function edit(Workout $workout): View
    {
        // Check multi-tenant isolation
        if ($workout->parent_id != parentId()) {
            abort(403, 'Unauthorized access');
        }

        $parentId = parentId();
        $members = Member::where('parent_id', $parentId)->active()->get();
        $trainers = Trainer::where('parent_id', $parentId)->active()->get();
        $workout->load('activities');

        return view('workouts.edit', compact('workout', 'members', 'trainers'));
    }

    /**
     * Update the specified workout
     */
    public function update(WorkoutPrescriptionRequest $request, Workout $workout): RedirectResponse
    {
        // Check multi-tenant isolation
        if ($workout->parent_id != parentId()) {
            abort(403, 'Unauthorized access');
        }

        $validated = $request->validated();
        $activities = $validated['activities'] ?? [];
        unset($validated['activities']);

        DB::transaction(function () use ($activities, $request, $validated, $workout) {
            $workout->update($validated);

            if ($request->boolean('sync_activities')) {
                $workout->activities()->delete();

                foreach (array_values($activities) as $index => $activity) {
                    $workout->activities()->create(array_merge($activity, [
                        'order' => $index,
                    ]));
                }
            }
        });

        return $this->redirectAfterPersist($workout, 'Treino atualizado com sucesso.');
    }

    private function redirectAfterPersist(Workout $workout, string $message): RedirectResponse
    {
        if ($workout->member_id) {
            return redirect()
                ->route('members.show', ['member' => $workout->member_id, 'tab' => 'workout'])
                ->with('success', $message);
        }

        return redirect()->route('workouts.index')
            ->with('success', $message);
    }

    /**
     * Remove the specified workout
     */
    public function destroy(Workout $workout)
    {
        // Check multi-tenant isolation
        if ($workout->parent_id != parentId()) {
            abort(403, 'Unauthorized access');
        }

        $workout->delete();

        return response()->json([
            'status' => true,
            'message' => 'Data deleted successfully',
        ]);
    }

    /**
     * Show today's workouts
     */
    public function today(): View
    {
        $parentId = parentId();
        $workouts = Workout::where('parent_id', $parentId)
            ->today()
            ->active()
            ->with(['member', 'activities'])
            ->get();

        return view('workouts.today', compact('workouts'));
    }
}
