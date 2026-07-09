<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class PrimeCoachTenantSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::role('owner')->first();

        Tenant::create([
            'user_id' => $owner->id,
            'business_name' => 'MGTEAM FITNESS & HEALTH',
            'subdomain' => 'prime',
            'status' => 'active',
            'max_members' => 50,
            'max_trainers' => 5,
            'trial_ends_at' => now()->addDays(7),
        ]);

        $this->command->info('✅ Tenant Prime Coaching criado');
    }
}
