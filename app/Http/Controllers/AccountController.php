<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function settings(): View
    {
        $user = auth()->user();
        $tenant = $user->hasRole('owner') || ! $user->parent_id
            ? $user
            : (\App\Models\User::find($user->parent_id) ?? $user);

        return view('mgteam.account.settings', [
            'user' => $user,
            'tenant' => $tenant,
            'companyName' => settings('app_name', config('brand.name', 'MGTEAM FITNESS & HEALTH')),
            'companyEmail' => settings('company_email', $tenant->email),
            'companyPhone' => settings('company_phone', ''),
            'companyCity' => settings('company_city', ''),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:40'],
            'company_city' => ['nullable', 'string', 'max:120'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => filled($validated['password'] ?? null)
                ? Hash::make($validated['password'])
                : $user->password,
        ]);

        if ($user->hasRole('owner') || $user->hasRole('manager')) {
            $parentId = parentId();
            foreach ([
                'app_name' => $validated['company_name'] ?? null,
                'company_email' => $validated['company_email'] ?? null,
                'company_phone' => $validated['company_phone'] ?? null,
                'company_city' => $validated['company_city'] ?? null,
            ] as $name => $value) {
                if ($value !== null && $value !== '') {
                    Setting::updateOrCreate(
                        ['parent_id' => $parentId, 'name' => $name],
                        ['value' => $value, 'type' => 'company']
                    );
                }
            }
            Cache::forget('settings_'.$parentId);
        }

        return back()->with('success', 'Perfil e dados da conta atualizados.');
    }
}
