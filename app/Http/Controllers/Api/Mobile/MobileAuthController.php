<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\User;
use App\Support\AdminCredentials;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MobileAuthController extends Controller
{
    public function professionalLogin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        /** @var User|null $user */
        $user = User::query()
            ->where('email', AdminCredentials::resolveEmail($validated['email']))
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'error' => 'Credenciais inválidas',
                'message' => 'Credenciais inválidas.',
            ], 401);
        }

        $tokenName = $validated['device_name'] ?? 'mobile-professional';
        $token = $user->createToken($tokenName, ['professional'])->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->mapProfessionalUser($user),
        ]);
    }

    public function clientLogin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        /** @var Member|null $member */
        $member = Member::query()
            ->withoutGlobalScopes()
            ->where('email', $validated['email'])
            ->first();

        if (! $member) {
            return response()->json([
                'error' => 'Cliente não encontrado',
                'message' => 'Credenciais inválidas.',
            ], 401);
        }

        $coach = $this->resolveMemberCoach($member);
        if (! $coach || ! Hash::check($validated['password'], $coach->password)) {
            return response()->json([
                'error' => 'Senha incorreta',
                'message' => 'Credenciais inválidas.',
            ], 401);
        }

        $tokenName = 'client-'.$member->id;
        $token = $coach->createToken($tokenName, ['client:'.$member->id])->plainTextToken;

        return response()->json([
            'token' => $token,
            'client' => $this->mapMember($member),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $member = $this->resolveClientMemberFromToken($request);
        if ($member) {
            return response()->json($this->mapMember($member));
        }

        /** @var User $user */
        $user = $request->user();

        return response()->json($this->mapProfessionalUser($user));
    }

    public function resolveClientMemberFromToken(Request $request): ?Member
    {
        $token = $request->user()?->currentAccessToken();
        if (! $token) {
            return null;
        }

        $memberId = null;
        if (Str::startsWith($token->name, 'client-')) {
            $memberId = (int) Str::after($token->name, 'client-');
        }

        if (! $memberId) {
            foreach ($token->abilities ?? [] as $ability) {
                if (Str::startsWith($ability, 'client:')) {
                    $memberId = (int) Str::after($ability, 'client:');
                    break;
                }
            }
        }

        if (! $memberId) {
            return null;
        }

        return Member::query()->find($memberId);
    }

    private function resolveMemberCoach(Member $member): ?User
    {
        if ($member->parent_id) {
            /** @var User|null $owner */
            $owner = User::query()->find($member->parent_id);
            if ($owner) {
                return $owner;
            }
        }

        if ($member->user_id) {
            /** @var User|null $user */
            $user = User::query()->find($member->user_id);
            if ($user) {
                return $user;
            }
        }

        /** @var User|null $fallback */
        $fallback = User::query()
            ->whereNotNull('email')
            ->where(function ($query) {
                $query->whereHas('roles', fn ($roleQuery) => $roleQuery->whereIn('name', ['owner', 'admin']))
                    ->orWhereNull('parent_id');
            })
            ->orderBy('id')
            ->first();

        return $fallback;
    }

    private function mapProfessionalUser(User $user): array
    {
        $role = ($user->hasRole('owner') || $user->hasRole('super-admin') || $user->hasRole('admin'))
            ? 'ADMIN'
            : 'COACH';

        return [
            'id' => (string) $user->id,
            'name' => (string) $user->name,
            'email' => (string) $user->email,
            'image' => $user->avatar,
            'role' => $role,
        ];
    }

    private function mapMember(Member $member): array
    {
        $coach = $this->resolveMemberCoach($member);

        return [
            'id' => (string) $member->id,
            'name' => (string) ($member->name ?? ''),
            'email' => $member->email,
            'photoUrl' => $member->photo,
            'image' => $member->photo,
            'phone' => $member->phone,
            'whatsapp' => $member->phone,
            'status' => $member->status,
            'coachName' => $coach?->name,
            'coach' => $coach ? [
                'id' => (string) $coach->id,
                'name' => $coach->name,
                'image' => $coach->avatar,
            ] : null,
        ];
    }
}
