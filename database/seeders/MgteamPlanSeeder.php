<?php

namespace Database\Seeders;

use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Database\Seeder;

class MgteamPlanSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::role('owner')->first();

        MembershipPlan::create([
            'parent_id' => $owner->id,
            'name' => 'Consultoria Online',
            'description' => 'Acompanhamento com treino personalizado e suporte via app',
            'price' => 197.00,
            'duration_type' => 'monthly',
            'duration_value' => 1,
            'is_active' => true,
            'features' => ['Treino personalizado', 'Vídeos dos exercícios', 'Suporte WhatsApp'],
            'max_classes' => null,
            'personal_training' => true,
        ]);

        $this->command->info('✅ 1 plano criado: Consultoria Online');
    }
}
