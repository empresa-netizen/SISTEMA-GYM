<?php

namespace App\Http\Controllers;

use App\DataTables\MemberDataTable;
use App\Http\Requests\MemberStoreRequest;
use App\Http\Requests\MemberUpdateRequest;
use App\Models\DietFood;
use App\Models\DietMenu;
use App\Models\LibraryWorkout;
use App\Models\Member;
use App\Models\MembershipPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MemberController extends Controller
{
    /**
     * Display a listing of members
     */
    public function index(Request $request, MemberDataTable $dataTable)
    {
        $parentId = parentId();
        $plans = MembershipPlan::where('parent_id', $parentId)->get();

        $query = Member::query()
            ->with([
                'membershipPlan',
                'anamnesis',
                'photos',
                'feedbacks',
                'workouts',
                'dietPrescriptions',
                'cardioPlans',
                'healthRecords',
            ])
            ->where('parent_id', $parentId);

        $status = $request->get('status', 'active');
        if ($status !== '' && $status !== null) {
            $query->where('status', $status);
        } else {
            $query->where('status', 'active');
        }

        if ($search = $request->get('search_value', $request->get('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('member_id', 'like', "%{$search}%");
            });
        }

        if ($plan = $request->get('plan')) {
            $query->where('membership_plan_id', $plan);
        }

        $members = $query->orderBy('name')->get();

        $pendingCount = $members->filter(function (Member $member) {
            $hasActiveWorkout = $member->workouts->contains(fn ($w) => $w->status === 'active');
            $hasPendingFeedback = $member->feedbacks->contains(fn ($f) => $f->status === 'pending');
            $hasPendingDiet = $member->dietPrescriptions->contains(fn ($d) => ($d->delivery_status ?? null) === 'PENDING');

            return ! $hasActiveWorkout || $hasPendingFeedback || $hasPendingDiet;
        })->count();

        $deliveredCount = max(0, $members->count() - $pendingCount);

        return $dataTable->render('members.index', compact('plans', 'members', 'pendingCount', 'deliveredCount'));
    }

    /**
     * Show the form for creating a new member
     */
    public function create(): View
    {
        $parentId = parentId();
        $plans = MembershipPlan::where('parent_id', $parentId)->active()->get();

        return view('members.create', compact('plans'));
    }

    public function store(MemberStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('members', 'public');
        }

        // Add parent_id
        $data['parent_id'] = parentId();

        // Calculate membership end date
        $plan = null;
        if ($data['membership_plan_id'] && $data['membership_start_date']) {
            $plan = MembershipPlan::find($data['membership_plan_id']);
            $data['membership_end_date'] = $plan->calculateExpiryDate($data['membership_start_date']);
        }

        $initialNote = $data['notes'] ?? null;
        unset($data['notes']);

        $member = Member::create($data);

        if (filled($initialNote)) {
            \App\Models\MemberNote::create([
                'parent_id' => parentId(),
                'member_id' => $member->id,
                'author_id' => auth()->id(),
                'body' => $initialNote,
                'noted_at' => now(),
            ]);
        }

        // Send welcome email to member
        sendNotificationEmail('member_create', $member->email, [
            'gym_name' => settings('app_name', 'FitHub'),
            'member_name' => $member->name,
            'member_id' => $member->member_id,
            'membership_plan' => $plan ? $plan->name : 'N/A',
            'expiry_date' => $member->membership_end_date ? $member->membership_end_date->format('M d, Y') : 'Lifetime',
        ]);

        return redirect()->route('members.index')
            ->with('success', 'Cliente criado com sucesso. ID: '.$member->member_id);
    }

    /**
     * Display the specified member
     */
    public function show(Member $member, Request $request): View
    {
        if ($member->parent_id != parentId()) {
            abort(403, 'Unauthorized access');
        }

        $tab = $request->get('tab', 'progress');
        $tabAliases = [
            'overview' => 'progress',
            'prescriptions' => 'workout',
            'logbook' => 'workout',
        ];
        $tab = $tabAliases[$tab] ?? $tab;

        $allowedTabs = [
            'progress',
            'appointments',
            'anamnesis',
            'reviews',
            'diet',
            'workout',
            'cardio',
            'exams',
            'feedbacks',
            'photos',
            'notes',
        ];
        if (! in_array($tab, $allowedTabs, true)) {
            $tab = 'progress';
        }

        $member->load([
            'membershipPlan',
            'workouts.activities',
            'anamnesis',
            'photos',
            'logbooks',
            'dietPrescriptions.dietMenu.meals.mealFoods.dietFood',
            'cardioPlans',
            'memberNotes.author',
            'coach',
            'feedbacks',
            'healthRecords',
            'appointments',
            'conversation',
        ]);

        $dietMenus = DietMenu::query()
            ->where('parent_id', parentId())
            ->with('meals.mealFoods.dietFood')
            ->orderBy('name')
            ->get();
        $dietFoods = DietFood::query()
            ->where('parent_id', parentId())
            ->orderBy('name')
            ->get(['id', 'name', 'food_group', 'calories', 'protein', 'carbs', 'fat']);
        $workoutTemplates = LibraryWorkout::query()
            ->where('parent_id', parentId())
            ->where('status', 'published')
            ->with('activities')
            ->orderBy('title')
            ->get();

        return view('members.show', compact('dietFoods', 'dietMenus', 'member', 'tab', 'workoutTemplates'));
    }

    public function workouts(Member $member, Request $request): View
    {
        $request->merge(['tab' => 'workout']);

        return $this->show($member, $request);
    }

    public function diet(Member $member, Request $request): View
    {
        $request->merge(['tab' => 'diet']);

        return $this->show($member, $request);
    }

    /**
     * Show the form for editing the specified member
     */
    public function edit(Member $member): View
    {
        // Check multi-tenant isolation
        if ($member->parent_id != parentId()) {
            abort(403, 'Unauthorized access');
        }

        $parentId = parentId();
        $plans = MembershipPlan::where('parent_id', $parentId)->active()->get();

        return view('members.edit', compact('member', 'plans'));
    }

    /**
     * Update the specified member
     */
    public function update(MemberUpdateRequest $request, Member $member): RedirectResponse
    {
        // Check multi-tenant isolation
        if ($member->parent_id != parentId()) {
            abort(403, 'Unauthorized access');
        }

        $data = $request->validated();
        $newNote = $data['notes'] ?? null;
        unset($data['notes']);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($member->photo) {
                Storage::disk('public')->delete($member->photo);
            }
            $data['photo'] = $request->file('photo')->store('members', 'public');
        }

        // Recalculate membership end date if plan or start date changed
        if (isset($data['membership_plan_id']) && isset($data['membership_start_date'])) {
            $plan = MembershipPlan::find($data['membership_plan_id']);
            $data['membership_end_date'] = $plan->calculateExpiryDate($data['membership_start_date']);
        }

        $member->update($data);

        if (filled($newNote)) {
            \App\Models\MemberNote::create([
                'parent_id' => parentId(),
                'member_id' => $member->id,
                'author_id' => auth()->id(),
                'body' => $newNote,
                'noted_at' => now(),
            ]);
        }

        return redirect()->route('members.index')
            ->with('success', 'Cliente atualizado com sucesso.');
    }

    /**
     * Remove the specified member
     */
    public function destroy(Member $member)
    {
        // Check multi-tenant isolation
        if ($member->parent_id != parentId()) {
            abort(403, 'Unauthorized access');
        }

        // Delete photo if exists
        if ($member->photo) {
            Storage::disk('public')->delete($member->photo);
        }

        $member->delete();

        return response()->json([
            'status' => true,
            'message' => 'Data deleted successfully',
        ]);
    }
}
