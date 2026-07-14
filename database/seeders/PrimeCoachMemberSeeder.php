<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Database\Seeder;

class PrimeCoachMemberSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::role('owner')->first();
        $plan = MembershipPlan::where('parent_id', $owner->id)->first();

        $clients = [
            [
                'name' => 'Ana Beatriz Santos',
                'email' => 'anabeatriz@gmail.com',
                'phone' => '11999990001',
                'gender' => 'female',
                'status' => 'active',
                'membership_start_date' => now()->subMonth(),
                'membership_end_date' => now()->addMonths(2),
            ],
            [
                'name' => 'Carlos Eduardo Lima',
                'email' => 'carlos.lima@gmail.com',
                'phone' => '11999990002',
                'gender' => 'male',
                'status' => 'active',
                'membership_start_date' => now()->subWeeks(2),
                'membership_end_date' => now()->addMonths(3),
            ],
        ];

        foreach ($clients as $client) {
            $memberUser = User::updateOrCreate(
                ['email' => $client['email']],
                [
                    'name' => $client['name'],
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                    'parent_id' => $owner->id,
                    'avatar' => 'avatar-1.jpg',
                ]
            );

            if (method_exists($memberUser, 'assignRole')) {
                try {
                    $memberUser->assignRole('member');
                } catch (\Throwable $e) {
                    // role may already exist
                }
            }

            Member::updateOrCreate(
                [
                    'parent_id' => $owner->id,
                    'email' => $client['email'],
                ],
                array_merge($client, [
                    'membership_plan_id' => $plan->id,
                    'user_id' => $memberUser->id,
                    'coach_user_id' => $owner->id,
                ])
            );
        }

        $this->command->info('✅ 2 clientes: anabeatriz@gmail.com + carlos.lima@gmail.com (iguais ao app)');
        $this->command->info('   📱 Login aluno: anabeatriz@gmail.com / password');
    }
}
