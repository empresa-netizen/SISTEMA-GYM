<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Seeder;

class MgteamEventSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::role('owner')->first();
        if (! $owner) {
            return;
        }

        if (Event::where('parent_id', $owner->id)->exists()) {
            return;
        }

        $members = Member::where('parent_id', $owner->id)->get();

        Event::create([
            'parent_id' => $owner->id,
            'title' => 'Avaliação física — '.$members->first()?->name,
            'description' => 'MGTEAMira avaliação e alinhamento de objetivos.',
            'start_time' => now()->addDays(2)->setTime(10, 0),
            'end_time' => now()->addDays(2)->setTime(11, 0),
            'location' => 'Online — Google Meet',
            'max_participants' => 1,
            'registered_count' => 0,
            'status' => 'scheduled',
        ]);

        Event::create([
            'parent_id' => $owner->id,
            'title' => 'Check-in mensal — '.$members->last()?->name,
            'description' => 'Revisão de treino, medidas e ajustes do plano.',
            'start_time' => now()->addDays(5)->setTime(18, 30),
            'end_time' => now()->addDays(5)->setTime(19, 0),
            'location' => 'Online',
            'max_participants' => 1,
            'registered_count' => 0,
            'status' => 'scheduled',
        ]);

        $this->command->info('✅ 2 eventos de agenda criados');
    }
}
