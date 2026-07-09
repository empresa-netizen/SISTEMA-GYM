<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PrimeCoachUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        $owner = User::updateOrCreate(
            ['email' => 'coach@mgteam.app'],
            [
                'name' => 'Coach MGTEAM',
                'password' => $password,
                'email_verified_at' => now(),
                'parent_id' => null,
                'avatar' => 'avatar-1.jpg',
            ]
        );

        if (! $owner->hasRole('owner')) {
            $owner->assignRole('owner');
        }

        // Remove e-mails legados duplicados se existirem (evita 2 coaches)
        User::whereIn('email', ['coach@primecoaching.com.br', 'admin@mgteam.app'])
            ->where('id', '!=', $owner->id)
            ->delete();

        $this->command->info('✅ Profissional unificado: coach@mgteam.app / password');
    }
}
