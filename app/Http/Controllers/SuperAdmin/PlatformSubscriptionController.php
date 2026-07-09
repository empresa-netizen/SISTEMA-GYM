<?php

namespace App\Http\Controllers\SuperAdmin;

use App\DataTables\PlatformSubscriptionDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StorePlatformSubscriptionRequest;
use App\Http\Requests\SuperAdmin\UpdatePlatformSubscriptionRequest;
use App\Models\PlatformSubscriptionTier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlatformSubscriptionController extends Controller
{
    /**
     * Display a listing of platform subscription tiers.
     */
    public function index(PlatformSubscriptionDataTable $dataTable)
    {
        // Ensure only super-admin can access
        abort_unless(auth()->user()->hasRole('super-admin'), 403);

        $tiers = PlatformSubscriptionTier::withCount('tenants')
            ->ordered()
            ->get();

        return $dataTable->render('super-admin.platform-subscriptions.index');

        //        return view('super-admin.platform-subscriptions.index', compact('tiers'));
    }

    /**
     * Show the form for creating a new tier.
     */
    public function create(): View
    {
        abort_unless(auth()->user()->hasRole('super-admin'), 403);

        return view('super-admin.platform-subscriptions.create');
    }

    /**
     * Store a newly created tier in storage.
     */
    public function store(StorePlatformSubscriptionRequest $request): RedirectResponse
    {
        $tier = PlatformSubscriptionTier::create($request->validated());

        return redirect()
            ->route('super-admin.platform-subscriptions.index')
            ->with('success', 'Platform subscription tier created successfully.');
    }

    /**
     * Display the specified tier.
     */
    public function show(PlatformSubscriptionTier $platformSubscription): View
    {
        abort_unless(auth()->user()->hasRole('super-admin'), 403);

        $platformSubscription->loadCount('tenants');
        $tenants = $platformSubscription->tenants()
            ->latest()
            ->paginate(20);

        return view('super-admin.platform-subscriptions.show', compact('platformSubscription', 'tenants'));
    }

    /**
     * Show the form for editing the specified tier.
     */
    public function edit(PlatformSubscriptionTier $platformSubscription): View
    {
        abort_unless(auth()->user()->hasRole('super-admin'), 403);

        return view('super-admin.platform-subscriptions.edit', compact('platformSubscription'));
    }

    /**
     * Update the specified tier in storage.
     */
    public function update(UpdatePlatformSubscriptionRequest $request, PlatformSubscriptionTier $platformSubscription): RedirectResponse
    {
        $platformSubscription->update($request->validated());

        return redirect()
            ->route('super-admin.platform-subscriptions.show', $platformSubscription)
            ->with('success', 'Platform subscription tier updated successfully.');
    }

    /**
     * Remove the specified tier from storage.
     */
    public function destroy(PlatformSubscriptionTier $platformSubscription): JsonResponse
    {
        abort_unless(auth()->user()->hasRole('super-admin'), 403);

        // Prevent deletion if tier has active tenants
        if ($platformSubscription->tenants()->where('status', 'active')->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot delete tier with active customers. Please reassign them first.',
            ]);
        }

        $platformSubscription->delete();

        return response()->json([
            'status' => true,
            'message' => 'Data deleted successfully',
        ]);
    }
}
