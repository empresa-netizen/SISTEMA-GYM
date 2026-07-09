<?php

namespace App\Http\Controllers;

use App\DataTables\UserDataTable;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(UserDataTable $dataTable)
    {
        $staffRoles = ['owner', 'manager', 'trainer', 'receptionist'];
        $roles = Role::whereIn('name', $staffRoles)->orderBy('name')->get();
        $parentId = parentId();

        $statsQuery = User::with('roles')
            ->where(function ($query) use ($parentId) {
                $query->where('parent_id', $parentId)
                    ->orWhere('id', $parentId);
            })
            ->whereHas('roles', function ($query) use ($staffRoles) {
                $query->whereIn('name', $staffRoles);
            });

        $collaboratorStats = [
            'total' => (clone $statsQuery)->count(),
            'active' => (clone $statsQuery)->whereNotNull('email_verified_at')->count(),
            'pending' => (clone $statsQuery)->whereNull('email_verified_at')->count(),
        ];

        return $dataTable->render('users.index', compact('roles', 'collaboratorStats'));
        //        return view('users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        $roles = Role::whereIn('name', ['owner', 'manager', 'trainer', 'receptionist'])->orderBy('name')->get();

        return view('users.create', compact('roles'));
    }

    public function store(UserStoreRequest $request): RedirectResponse
    {
        $password = $request->password; // Store before hashing for email

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($password),

            'avatar' => $request->avatar,
            'parent_id' => $request->parent_id ?? parentId(),
            'email_verified_at' => now(), // Auto-verify admin-created users
        ]);

        // Assign role
        $user->assignRole($request->role);

        // Send welcome email
        sendNotificationEmail('user_create', $user->email, [
            'gym_name' => settings('app_name', 'FitHub'),
            'user_name' => $user->name,
            'email' => $user->email,
            'password' => $password,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): View
    {
        // Check multi-tenant isolation
        if ($user->parent_id != parentId() && $user->id != parentId()) {
            abort(403, 'Unauthorized access');
        }

        $user->load('roles', 'permissions', 'loggedHistories');

        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        // Check multi-tenant isolation
        if ($user->parent_id != parentId() && $user->id != parentId()) {
            abort(403, 'Unauthorized access');
        }

        $roles = Role::whereIn('name', ['owner', 'manager', 'trainer', 'receptionist'])->orderBy('name')->get();

        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UserUpdateRequest $request, User $user): RedirectResponse
    {
        // Check multi-tenant isolation
        if ($user->parent_id != parentId() && $user->id != parentId()) {
            abort(403, 'Unauthorized access');
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,

            'avatar' => $request->avatar,
        ];

        // Only update password if provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Update role
        $user->syncRoles([$request->role]);

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        // Check multi-tenant isolation
        if ($user->parent_id != parentId() && $user->id != parentId()) {
            abort(403, 'Unauthorized access');
        }

        // Prevent deleting yourself
        if ($user->id == auth()->id()) {
            return response()->json([
                'status' => false,
                'message' => 'You cannot delete your own account',
            ]);

        }

        $user->delete();

        return response()->json([
            'status' => true,
            'message' => 'Data deleted successfully',
        ]);
    }
}
