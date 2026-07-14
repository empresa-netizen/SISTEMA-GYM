<?php

namespace Database\Seeders;

use App\Models\DietPrescription;
use App\Models\Event;
use App\Models\Member;
use App\Models\MemberAnamnesis;
use App\Models\MemberLogbook;
use App\Models\User;
use Illuminate\Database\Seeder;

class MgteamCrmSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::role('owner')->first();
        $members = Member::where('parent_id', $owner?->id)->get();
        if ($members->isEmpty()) {
            return;
        }

        foreach ($members as $member) {
            MemberAnamnesis::firstOrCreate(
                ['parent_id' => $owner->id, 'member_id' => $member->id],
                [
                    'goals' => 'Hipertrofia e recomposição corporal.',
                    'injuries' => 'Sem lesões ativas.',
                    'lifestyle' => 'Treina 4x/semana, sono irregular.',
                    'status' => 'completed',
                ]
            );

            if (! MemberLogbook::where('member_id', $member->id)->exists()) {
                MemberLogbook::create([
                    'parent_id' => $owner->id,
                    'member_id' => $member->id,
                    'type' => 'TRAINING',
                    'title' => 'Treino A concluído',
                    'logged_at' => now()->subDays(1),
                    'rating' => 5,
                    'comment' => 'Boa execução nos exercícios.',
                ]);
            }
        }

        $ana = $members->first();
        if ($ana && ! DietPrescription::where('member_id', $ana->id)->exists()) {
            $menu = \App\Models\DietMenu::where('parent_id', $owner->id)->first();
            DietPrescription::create([
                'parent_id' => $owner->id,
                'member_id' => $ana->id,
                'diet_menu_id' => $menu?->id,
                'title' => 'Plano alimentar — Fase 1',
                'status' => 'sent',
                'delivery_status' => 'DELIVERED',
                'scheduled_at' => now()->subDays(3),
                'sent_at' => now()->subDays(3),
            ]);
        }

        Event::whereNull('member_id')->get()->each(function ($event, $i) use ($members) {
            $member = $members[$i % $members->count()] ?? null;
            if ($member) {
                $event->update(['member_id' => $member->id]);
            }
        });

        $this->command->info('✅ CRM cliente: anamnese, diário, dieta e consultas vinculadas');
    }
}
