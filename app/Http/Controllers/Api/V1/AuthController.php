<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserResource;
use App\Models\Member;
use App\Models\User;
use App\Support\AdminCredentials;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
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
            return $this->loginMember($validated);
        }

        $tokenName = $validated['device_name'] ?? 'mobile-app';
        $token = $user->createToken($tokenName)->plainTextToken;

        return response()->json([
            'message' => 'Autenticacao realizada com sucesso.',
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    /**
     * @param  array{email: string, password: string, device_name?: string|null}  $validated
     */
    private function loginMember(array $validated): JsonResponse
    {
        /** @var Member|null $member */
        $member = Member::query()
            ->withoutGlobalScopes()
            ->where('email', $validated['email'])
            ->first();

        $coach = $member ? $this->resolveMemberCoach($member) : null;

        if (! $member || ! $coach || ! Hash::check($validated['password'], $coach->password)) {
            return response()->json([
                'message' => 'Credenciais invalidas.',
                'errors' => [
                    'email' => ['As credenciais informadas estao incorretas.'],
                ],
            ], 401);
        }

        $token = $coach->createToken('client-'.$member->id, ['client:'.$member->id])->plainTextToken;
        $mappedMember = $this->mapMember($member, $coach);

        return response()->json([
            'message' => 'Autenticacao realizada com sucesso.',
            'token_type' => 'Bearer',
            'access_token' => $token,
            'session_type' => 'student',
            'user' => $mappedMember,
            'client' => $mappedMember,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($request->user()),
        ]);
    }

    public function refresh(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Token renovado com sucesso.',
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        if ($request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => 'Logout realizado com sucesso.',
        ]);
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

    /**
     * @return array<string, mixed>
     */
    private function mapMember(Member $member, User $coach): array
    {
        return [
            'id' => (string) $member->id,
            'name' => (string) ($member->name ?? ''),
            'email' => $member->email,
            'photoUrl' => $member->photo,
            'image' => $member->photo,
            'phone' => $member->phone,
            'whatsapp' => $member->phone,
            'status' => $member->status,
            'role' => 'STUDENT',
            'coachName' => $coach->name,
            'coach' => [
                'id' => (string) $coach->id,
                'name' => $coach->name,
                'image' => $coach->avatar,
            ],
        ];
    }
}
