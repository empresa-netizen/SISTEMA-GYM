<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate table
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('members')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $owner = User::role('owner')->first();

        if (! $owner) {
            $this->command->warn('No owner user found. Please run UserSeeder first.');

            return;
        }

        $parentId = $owner->id;
        $plans = MembershipPlan::where('parent_id', $parentId)->get();

        if ($plans->isEmpty()) {
            $this->command->warn('No membership plans found. Please run MembershipPlanSeeder first.');

            return;
        }

        $members = [
            // Active members with various plans
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah.johnson@example.com',
                'phone' => '555-0101',
                'date_of_birth' => '1990-05-15',
                'gender' => 'female',
                'address' => '123 Fitness Street, Gym City',
                'emergency_contact_name' => 'Mike Johnson',
                'emergency_contact_phone' => '555-0102',
                'status' => 'active',
                'membership_start_date' => now()->subMonths(3),
                'membership_end_date' => now()->addMonths(9),
            ],
            [
                'name' => 'Michael Chen',
                'email' => 'michael.chen@example.com',
                'phone' => '555-0103',
                'date_of_birth' => '1985-08-22',
                'gender' => 'male',
                'address' => '456 Muscle Ave, Power Town',
                'emergency_contact_name' => 'Lisa Chen',
                'emergency_contact_phone' => '555-0104',
                'status' => 'active',
                'membership_start_date' => now()->subMonths(6),
                'membership_end_date' => now()->addMonths(6),
            ],
            [
                'name' => 'Emily Rodriguez',
                'email' => 'emily.rodriguez@example.com',
                'phone' => '555-0105',
                'date_of_birth' => '1992-03-10',
                'gender' => 'female',
                'address' => '789 Cardio Lane, Health Valley',
                'status' => 'active',
                'membership_start_date' => now()->subMonths(1),
                'membership_end_date' => now()->addMonths(11),
            ],
            [
                'name' => 'David Thompson',
                'email' => 'david.thompson@example.com',
                'phone' => '555-0106',
                'date_of_birth' => '1988-11-30',
                'gender' => 'male',
                'address' => '321 Strength Road',
                'status' => 'active',
                'membership_start_date' => now()->subMonths(2),
                'membership_end_date' => now()->addMonths(10),
            ],
            [
                'name' => 'Jessica Martinez',
                'email' => 'jessica.martinez@example.com',
                'phone' => '555-0107',
                'date_of_birth' => '1995-07-18',
                'gender' => 'female',
                'status' => 'active',
                'membership_start_date' => now()->subDays(15),
                'membership_end_date' => now()->addMonths(1)->subDays(15),
            ],
            [
                'name' => 'Robert Wilson',
                'email' => 'robert.wilson@example.com',
                'phone' => '555-0108',
                'date_of_birth' => '1982-01-25',
                'gender' => 'male',
                'address' => '567 Workout Blvd',
                'medical_conditions' => 'Mild asthma - uses inhaler before workouts',
                'status' => 'active',
                'membership_start_date' => now()->subMonths(4),
                'membership_end_date' => now()->addMonths(8),
            ],
            [
                'name' => 'Amanda Lee',
                'email' => 'amanda.lee@example.com',
                'phone' => '555-0109',
                'date_of_birth' => '1991-09-12',
                'gender' => 'female',
                'status' => 'active',
                'membership_start_date' => now()->subMonths(5),
                'membership_end_date' => now()->addMonths(7),
            ],
            [
                'name' => 'Christopher Brown',
                'email' => 'chris.brown@example.com',
                'phone' => '555-0110',
                'date_of_birth' => '1987-04-08',
                'gender' => 'male',
                'address' => '890 Gym Lane',
                'status' => 'active',
                'membership_start_date' => now()->subWeeks(2),
                'membership_end_date' => now()->addMonths(1)->subWeeks(2),
            ],
            [
                'name' => 'Michelle Davis',
                'email' => 'michelle.davis@example.com',
                'phone' => '555-0111',
                'date_of_birth' => '1993-12-03',
                'gender' => 'female',
                'status' => 'active',
                'membership_start_date' => now()->subMonths(8),
                'membership_end_date' => now()->addMonths(4),
            ],
            [
                'name' => 'James Garcia',
                'email' => 'james.garcia@example.com',
                'phone' => '555-0112',
                'date_of_birth' => '1980-06-20',
                'gender' => 'male',
                'address' => '234 Exercise Way',
                'emergency_contact_name' => 'Maria Garcia',
                'emergency_contact_phone' => '555-0113',
                'status' => 'active',
                'membership_start_date' => now()->subMonths(10),
                'membership_end_date' => now()->addMonths(2),
            ],
            // Members expiring soon
            [
                'name' => 'Sophia Anderson',
                'email' => 'sophia.anderson@example.com',
                'phone' => '555-0114',
                'date_of_birth' => '1994-02-14',
                'gender' => 'female',
                'status' => 'active',
                'membership_start_date' => now()->subMonths(1)->subDays(25),
                'membership_end_date' => now()->addDays(5), // Expiring in 5 days
                'notes' => 'Membership expiring soon - send renewal reminder',
            ],
            [
                'name' => 'Daniel Kim',
                'email' => 'daniel.kim@example.com',
                'phone' => '555-0115',
                'date_of_birth' => '1989-10-28',
                'gender' => 'male',
                'status' => 'active',
                'membership_start_date' => now()->subMonths(3),
                'membership_end_date' => now()->addDays(10), // Expiring in 10 days
            ],
            // Expired members
            [
                'name' => 'Rachel Turner',
                'email' => 'rachel.turner@example.com',
                'phone' => '555-0116',
                'date_of_birth' => '1986-08-05',
                'gender' => 'female',
                'status' => 'expired',
                'membership_start_date' => now()->subMonths(4),
                'membership_end_date' => now()->subDays(15), // Expired 15 days ago
                'notes' => 'Expired - attempted to contact for renewal',
            ],
            [
                'name' => 'Kevin White',
                'email' => 'kevin.white@example.com',
                'phone' => '555-0117',
                'date_of_birth' => '1983-05-17',
                'gender' => 'male',
                'status' => 'expired',
                'membership_start_date' => now()->subMonths(5),
                'membership_end_date' => now()->subMonths(1), // Expired 1 month ago
            ],
            // Inactive members
            [
                'name' => 'Lauren Taylor',
                'email' => 'lauren.taylor@example.com',
                'phone' => '555-0118',
                'date_of_birth' => '1997-01-22',
                'gender' => 'female',
                'status' => 'inactive',
                'membership_start_date' => now()->subMonths(6),
                'membership_end_date' => now()->addMonths(6),
                'notes' => 'Requested temporary freeze due to travel',
            ],
            // Suspended member
            [
                'name' => 'Brian Harris',
                'email' => 'brian.harris@example.com',
                'phone' => '555-0119',
                'date_of_birth' => '1984-11-11',
                'gender' => 'male',
                'status' => 'suspended',
                'membership_start_date' => now()->subMonths(2),
                'membership_end_date' => now()->addMonths(10),
                'notes' => 'Suspended due to payment issues',
            ],
            // More active members
            [
                'name' => 'Nicole Scott',
                'email' => 'nicole.scott@example.com',
                'phone' => '555-0120',
                'date_of_birth' => '1996-04-30',
                'gender' => 'female',
                'status' => 'active',
                'membership_start_date' => now()->subMonths(7),
                'membership_end_date' => now()->addMonths(5),
            ],
            [
                'name' => 'Andrew Clark',
                'email' => 'andrew.clark@example.com',
                'phone' => '555-0121',
                'date_of_birth' => '1981-09-08',
                'gender' => 'male',
                'address' => '456 Training Center Drive',
                'status' => 'active',
                'membership_start_date' => now()->subMonths(11),
                'membership_end_date' => now()->addMonth(),
            ],
            [
                'name' => 'Stephanie Lewis',
                'email' => 'stephanie.lewis@example.com',
                'phone' => '555-0122',
                'date_of_birth' => '1990-12-25',
                'gender' => 'female',
                'status' => 'active',
                'membership_start_date' => now()->subMonths(9),
                'membership_end_date' => now()->addMonths(3),
            ],
            [
                'name' => 'Mark Robinson',
                'email' => 'mark.robinson@example.com',
                'phone' => '555-0123',
                'date_of_birth' => '1977-07-04',
                'gender' => 'male',
                'medical_conditions' => 'High blood pressure - cleared for moderate exercise',
                'status' => 'active',
                'membership_start_date' => now()->subYear(),
                'membership_end_date' => now()->addDays(30), // Annual expiring soon
            ],
            [
                'name' => 'Jennifer Moore',
                'email' => 'jennifer.moore@example.com',
                'phone' => '555-0124',
                'date_of_birth' => '1998-03-19',
                'gender' => 'female',
                'status' => 'active',
                'membership_start_date' => now()->subDays(7),
                'membership_end_date' => now()->addMonths(1)->subDays(7),
            ],
            [
                'name' => 'Ryan Adams',
                'email' => 'ryan.adams@example.com',
                'phone' => '555-0125',
                'date_of_birth' => '1979-08-14',
                'gender' => 'male',
                'status' => 'active',
                'membership_start_date' => now()->subMonths(2),
                'membership_end_date' => now()->addMonths(10),
            ],
            [
                'name' => 'Ashley Young',
                'email' => 'ashley.young@example.com',
                'phone' => '555-0126',
                'date_of_birth' => '1999-06-01',
                'gender' => 'female',
                'status' => 'active',
                'membership_start_date' => now()->subDays(3),
                'membership_end_date' => now()->addMonths(1)->subDays(3),
            ],
            [
                'name' => 'Eric Nelson',
                'email' => 'eric.nelson@example.com',
                'phone' => '555-0127',
                'date_of_birth' => '1985-02-28',
                'gender' => 'male',
                'status' => 'active',
                'membership_start_date' => now()->subMonths(4),
                'membership_end_date' => now()->addMonths(8),
            ],
            [
                'name' => 'Melissa Hall',
                'email' => 'melissa.hall@example.com',
                'phone' => '555-0128',
                'date_of_birth' => '1992-10-10',
                'gender' => 'female',
                'status' => 'active',
                'membership_start_date' => now()->subMonths(5),
                'membership_end_date' => now()->addMonths(7),
            ],
        ];

        foreach ($members as $index => $memberData) {
            // Assign a random membership plan
            $plan = $plans->random();
            $noteBody = $memberData['notes'] ?? null;
            unset($memberData['notes']);

            $member = Member::create(array_merge($memberData, [
                'parent_id' => $parentId,
                'membership_plan_id' => $plan->id,
            ]));

            if (filled($noteBody)) {
                \App\Models\MemberNote::create([
                    'parent_id' => $parentId,
                    'member_id' => $member->id,
                    'author_id' => null,
                    'body' => $noteBody,
                    'noted_at' => now(),
                ]);
            }
        }

        $this->command->info('✅ '.count($members).' members created successfully!');
    }
}
