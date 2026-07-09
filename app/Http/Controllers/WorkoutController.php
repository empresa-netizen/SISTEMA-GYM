<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Trainer;
use App\Models\Workout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'member_id' => 'nullable|exists:members,id',
            'trainer_id' => 'nullable|exists:trainers,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'workout_date' => 'nullable|date',
            'status' => 'required|in:active,completed,cancelled',
            'notes' => 'nullable|string',
            // Activities
            'activities' => 'nullable|array',
            'activities.*.exercise_name' => 'required|string|max:255',
            'activities.*.description' => 'nullable|string',
            'activities.*.sets' => 'nullable|integer|min:1',
            'activities.*.reps' => 'nullable|integer|min:1',
            'activities.*.duration_minutes' => 'nullable|integer|min:1',
            'activities.*.rest_seconds' => 'nullable|integer|min:0',
            'activities.*.weight_kg' => 'nullable|numeric|min:0',
        ]);

        $validated['parent_id'] = parentId();

        // Create workout
        $workout = Workout::create($validated);

        // Create activities
        $activitiesCount = 0;
        if ($request->has('activities')) {
            foreach ($request->activities as $index => $activity) {
                $activity['order'] = $index;
                $workout->activities()->create($activity);
                $activitiesCount++;
            }
        }

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

        return redirect()->route('workouts.index')
            ->with('success', 'Workout created successfully with ID: '.$workout->workout_id);
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
    public function update(Request $request, Workout $workout): RedirectResponse
    {
        // Check multi-tenant isolation
        if ($workout->parent_id != parentId()) {
            abort(403, 'Unauthorized access');
        }

        $validated = $request->validate([
            'member_id' => 'nullable|exists:members,id',
            'trainer_id' => 'nullable|exists:trainers,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'workout_date' => 'nullable|date',
            'status' => 'required|in:active,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        $workout->update($validated);

        return redirect()->route('workouts.index')
            ->with('success', 'Workout updated successfully');
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
