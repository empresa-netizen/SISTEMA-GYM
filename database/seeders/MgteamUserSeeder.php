<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\AdminCredentials;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MgteamUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        $owner = User::updateOrCreate(
            ['email' => AdminCredentials::CANONICAL_EMAIL],
            [
                'name' => 'Coach MGTEAM',
                'password' => $password,
                'parent_id' => null,
                'avatar' => 'avatar-1.jpg',
                'twofa_enabled' => false,
                'twofa_secret' => null,
                'code' => null,
            ]
        );

        $owner->forceFill([
            'email_verified_at' => now(),
            'password' => $password,
            'parent_id' => null,
            'twofa_enabled' => false,
            'twofa_secret' => null,
            'code' => null,
        ])->save();

        if (! $owner->hasRole('owner')) {
            $owner->assignRole('owner');
        }

        // Remove e-mails legados duplicados se existirem (evita 2 coaches).
        User::whereIn('email', [
            AdminCredentials::LEGACY_COACH_EMAIL,
            AdminCredentials::LEGACY_ADMIN_EMAIL,
        ])
            ->where('id', '!=', $owner->id)
            ->delete();

        $this->command->info('✅ Admin/profissional: coach@mgteam.app ou admin@mgteam.app / password');
    }
}
